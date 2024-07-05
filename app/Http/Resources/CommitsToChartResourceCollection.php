<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommitsToChartResourceCollection extends ResourceCollection
{
    protected $since;
    protected $until;

    public function since($value)
    {
        $this->since = $value;
        return $this;
    }

    public function until($value)
    {
        $this->until = $value;
        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->since === null) {
            $this->since = Carbon::now()->subDays(90);
        }

        if ($this->until === null) {
            $this->until = Carbon::now();
        }

        $data = [];

        $period = Carbon::parse($this->since)->daysUntil(Carbon::parse($this->until))->toArray();
        $dates = array_map(fn($date) => $date->format('Y-m-d'), $period);

        foreach ($this->collection as $commit) {
            $author = $commit['author_name'];
            if (!isset($data[$author])) {
                $data[$author] = array_fill_keys($dates, 0);
            }
        }

        $totalCommits = 0;
        foreach ($this->collection as $commit) {
            $author = $commit['author_name'];
            $date = $commit['created_at_date'];
            if (isset($data[$author][$date])) {
                $totalCommits += $commit['number_commits'];
                $data[$author][$date] += $commit['number_commits'];
            }
        }

        $result = [];
        foreach ($data as $author => $commits) {
            $result[] = [
                'label' => $author,
                'data' => array_values($commits),
                'fill' => true,
                'backgroundColor' => 'rgba(59, 130, 246, 0.08)',
                'borderColor' => 'rgb(99, 102, 241)',
                'borderWidth' => 2,
                'tension' => 0.15,
                'pointRadius' => 0,
                'pointHoverRadius' => 3,
                'pointBackgroundColor' => 'rgb(99, 102, 241)',
            ];
        }

        return [
            "datasets" => $result,
            "labels" => $dates,
            "totalCommits" => $totalCommits,
            "totalContributors" => count($result),
            "avgCommitsContributor" => $totalCommits/count($result),
            "avgCommitsDay" => number_format($totalCommits/count($dates), 2),
        ];
    }
}
