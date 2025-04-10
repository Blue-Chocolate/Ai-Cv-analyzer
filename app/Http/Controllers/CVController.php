<?php
namespace App\Http\Controllers;
    
    use App\Models\CV;
    use Smalot\PdfParser\Parser;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Log;
    

    class CVController extends Controller
    {
        private array $skillKeywords = ['php', 'laravel', 'javascript', 'html', 'css', 'mysql'];
        private array $softSkillKeywords = ['teamwork', 'communication', 'leadership', 'problem solving'];
        private array $educationKeywords = ['bachelor', 'master', 'phd', 'degree', 'university'];
        private array $languageKeywords = ['english', 'spanish', 'french', 'german', 'chinese', 'japanese'];

        public function index()
        {
            $cvs = CV::orderBy('created_at', 'desc')->get();
            return view('cvs.index', compact('cvs'));
        }

        public function store(Request $request)
        {
            $request->validate([
                'cv' => 'required|mimes:pdf|max:10240'
            ]);

            $file = $request->file('cv');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('cvs', $fileName, 'public');

            try {
                $parser = new Parser();
                $pdf = $parser->parseFile(storage_path('app/public/' . $path));
                $text = $pdf->getText();

                Log::info('PDF Text Extraction', [
                    'filename' => $fileName,
                    'text_length' => strlen($text),
                    'extract_sample' => substr($text, 0, 500) . '...',
                    'full_text' => $text
                ]);

                if (empty(trim($text))) {
                    throw new \Exception('No text could be extracted from the PDF');
                }

                $summary = $this->getSummary($text);

                $experienceYears = $this->calculateExperienceYears($text);
                $skillScore = $this->calculateScore($text, $this->skillKeywords);
                $softSkills = $this->calculateScore($text, $this->softSkillKeywords);
                $educationScore = $this->calculateScore($text, $this->educationKeywords);
                $relevantExperience = $this->calculateRelevantExperience($text);

                $cv = CV::create([
                    'name' => $fileName,
                    'path' => $path,
                    'summary' => $summary,
                    'experience_years' => $experienceYears,
                    'skill_score' => $skillScore,
                    'soft_skills' => $softSkills,
                    'education_score' => $educationScore,
                    'relevant_experience' => $relevantExperience
                ]);

                $cv->calculateFitScore();

                return redirect()->route('cvs.index')->with('success', 'CV uploaded and analyzed successfully!');
            } catch (\Exception $e) {
                \Log::error('PDF Processing Error', [
                    'filename' => $fileName,
                    'error' => $e->getMessage()
                ]);

                return redirect()->route('cvs.index')
                    ->with('error', 'Could not process the PDF file. Please try again with a different file.');
            }
        }

        private function getSummary(string $text): string
        {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('HUGGINGFACE_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api-inference.huggingface.co/models/facebook/bart-large-cnn', [
                'inputs' => $text,
                'parameters' => [
                    'max_length' => 300,
                    'min_length' => 100,
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result[0]['summary_text'] ?? 'Summary not available';
            }

            return 'Error generating summary';
        }

        private function calculateExperienceYears(string $text): int
        {
            \Log::debug('Starting experience calculation', ['text' => $text]);

            $currentYear = (int)date('Y');
            $datePattern = '/(\d{4})\s*(?:-|to|–|–|\/)\s*(present|current|now|\d{4})/i';
            preg_match_all($datePattern, $text, $matches, PREG_SET_ORDER);

            $totalYears = 0;
            $processedPeriods = [];

            foreach ($matches as $match) {
                $startYear = (int)$match[1];
                $endYear = strtolower($match[2]) === 'present' || 
                        strtolower($match[2]) === 'current' || 
                        strtolower($match[2]) === 'now' 
                        ? $currentYear 
                        : (int)$match[2];

                if ($startYear > $currentYear || $endYear > $currentYear || $startYear > $endYear) {
                    continue;
                }

                $period = $startYear . '-' . $endYear;
                if (!in_array($period, $processedPeriods)) {
                    $processedPeriods[] = $period;
                    $yearDiff = $endYear - $startYear;
                    $totalYears += $yearDiff;

                    \Log::debug('Processing period', [
                        'period' => $period,
                        'years' => $yearDiff,
                        'running_total' => $totalYears
                    ]);
                }
            }

            $experiencePattern = '/(\d+)\s*(?:\+)?\s*years?\s+(?:of\s+)?experience/i';
            $explicitYears = 0;
            if (preg_match($experiencePattern, $text, $matches)) {
                $explicitYears = (int)$matches[1];
                \Log::debug('Found explicit experience statement', ['years' => $explicitYears]);
            }

            if ($explicitYears > 0 && ($totalYears === 0 || abs($explicitYears - $totalYears) <= 2)) {
                $totalYears = $explicitYears;
            }

            \Log::info('Experience calculation results', [
                'date_ranges_total' => $totalYears,
                'explicit_statement' => $explicitYears,
                'final_total' => $totalYears
            ]);

            return max(0, min($totalYears, 50));
        }

        private function calculateScore(string $text, array $keywords): int
        {
            $score = 0;
            $text = strtolower($text);
            
            foreach ($keywords as $keyword) {
                if (str_contains($text, strtolower($keyword))) {
                    $score += 20;
                }
            }

            return min($score, 100);
        }

        private function calculateRelevantExperience(string $text): int
        {
            $relevantKeywords = array_merge($this->skillKeywords, ['developer', 'software', 'web']);
            return $this->calculateScore($text, $relevantKeywords);
        }

        public function destroy(CV $cv)
        {
            Storage::disk('public')->delete($cv->path);
            $cv->delete();
            return redirect()->route('cvs.index')->with('success', 'CV deleted successfully!');
        }
    }
