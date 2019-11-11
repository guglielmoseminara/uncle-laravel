<?php

namespace UncleProject\UncleLaravel\Traits;

use Spatie\Sluggable\HasSlug as SpatieHasSlug;

trait HasSlug {

    use SpatieHasSlug;

    protected function otherRecordExistsWithSlug(string $slug): bool {
        $key = $this->getKey();
        $table = $this->getTable();
        if ($this->incrementing) {
            $key = $key ?? '0';
        }
        $query = static::where($this->slugOptions->slugField, $slug)
            ->where($table.".".$this->getKeyName(), '!=', $key);
            //->withoutGlobalScopes();

        if ($this->usesSoftDeletes()) {
            $query->withTrashed();
        }
        return $query->exists();
    }
}