<?php

namespace UncleProject\UncleLaravel\Traits;

trait RelatedJoin {


    public function getJoinField($relations)
    {
        $relations = explode('.', $relations);
        $column    = end($relations);
        $baseModel = $this->getModel();
        $baseTable = $baseModel->getTable();
        $basePrimaryKey = $baseModel->getKeyName();

        $currentModel      = $baseModel;
        $currentTableAlias = $baseTable;

        foreach ($relations as $relation) {
            if ($relation == $column) {
                break;
            }

            $relatedRelation   = $currentModel->$relation();
            $relatedModel      = $relatedRelation->getRelated();
            $relatedPrimaryKey = $relatedModel->getKeyName();
            $relatedTable      = $relatedModel->getTable();
            $relatedTableAlias = $this->useTableAlias ? sha1($relatedTable) : $relatedTable;
            $currentModel      = $relatedModel;
            $currentTableAlias = $relatedTableAlias;
        }
        return $currentTableAlias.'.'.$column;
    }

}