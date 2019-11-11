<?php

namespace UncleProject\UncleLaravel\Traits;

use Naoray\LaravelReviewable\Models\Review;
use App\Helpers\ReviewFactory;
use Naoray\LaravelReviewable\Traits\HasReviews as NaorayHasReviews;

trait HasReviews
{
    use NaorayHasReviews;

    /**
     * Create a review for this model.
     *
     * @param int    $score
     * @param string $body
     * @param model  $author
     *
     * @return Review
     */
    public function createReview($score, $body = null, $title = null, $author = null)
    {
        return ReviewFactory::create($this, $score, $body, $title, $author);
    }

    /**
     * Get the average score value.
     *
     * @return int
     */
    public function getScorePercentage($score)
    {
        $totalCount = $this->reviews()->count();
        if($totalCount == 0) {
            $totalCount = 1;
        }
        return intval(($this->reviews()->where('score', $score)->count() / $totalCount) * 100);
    }
}
