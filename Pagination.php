<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 10-Jan-19
 * Time: 1:46 PM
 */

/*
 * Pagination Class:
 * - Find $start and $end values for pageNum
 * - Find next/previous button requirement (last page doesn't need next button)
 * - Find which page numbers required as part of html output.
 *
 * :: Database call separate
 *
 * select * from table where id >= $start AND $id <= $end
 *
 */

class Pagination
{
    private  $perPage = 10;


    function forCurrentPage($pageNum=1)
    {
        $start = $this->perPage * ($pageNum - 1) + 1;
        return $start;
    }


    function isPreviousRequired($currentPage)
    {
        if ($currentPage === 1) {
            return false;
        }
        return true;
    }

    function isNextRequired($currentPage, $total)
    {
        $lastPage = $this->getLastPage($total);

        if ($currentPage >= $lastPage) {
            return false;
        }
        return true;
    }

    function pageNumbersToShow($currentPage,$numItemsOnPagination = 5, $totalResults)
    {
        $lastPage = $this->getLastPage($totalResults);
        $targetSet = (int) ceil($currentPage / $numItemsOnPagination) ;

        $start = $numItemsOnPagination * ($targetSet - 1) ;
        if ($lastPage < $start + $numItemsOnPagination){
            $end = $lastPage;
        }
        else {
            $end = $start + $numItemsOnPagination;
        }

        $pageNumArray = range($start+1,$end);

        return $pageNumArray;

    }


    /**
     * @param $totalResults
     * @return float
     */
    public function getLastPage($totalResults)
    {
        $lastPage = ceil($totalResults / $this->perPage);
        return $lastPage;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @param int $perPage
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
    }

}