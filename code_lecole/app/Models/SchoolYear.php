<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// The school years and their terms (school_years, school_terms). The current year = the newest one the school has
// started; its terms have start and end dates (the Academic page's School Year section will edit them: B-13).
class SchoolYear extends Model
{
    public function current(): int
    {
        return (int) $this->db->query('SELECT MAX(year) FROM school_years')->fetchColumn();
    }

    // A year's terms in order: [term number => ['starts_on' => '2026-01-02', 'ends_on' => '2026-04-10']]
    public function terms(int $year): array
    {
        $statement = $this->db->prepare('SELECT term, starts_on, ends_on FROM school_terms WHERE year = :year ORDER BY term');
        $statement->execute(['year' => $year]);

        return array_column($statement->fetchAll(), null, 'term');
    }

    // The current year's terms that have started (today = PHP's date), in order: [1, 2, 3]
    public function startedTerms(): array
    {
        $today = date('Y-m-d');

        return array_keys(array_filter($this->terms($this->current()), fn (array $term): bool => $term['starts_on'] <= $today));
    }
}
