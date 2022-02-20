<?php

namespace App\Service;

class Paginate{

    private $itemsPerPage;
    private $callingInstance;
    private $dataSet;

    public function __construct($itemsPerPage, $dataSet, $callingInstance)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->dataSet = $dataSet;
        $this->callingInstance = $callingInstance;
    }

    /**
     * 
     * @param $page is the page that the user ants o fetch
     * @param $getDataSet is the set of data to be paginated
     * 
     * @return
     *       'data' => $data, 
     *       'prevPage' => $prevPage, 
     *       'page' => $page, 
     *       'nextPage' => $nextPage,
     *       'totalPages' => $totalPages
     * 
     */
    public function fetchPage($page, $getDataSet)
    {
        $totalPages = ceil(count($this->dataSet) / $this->itemsPerPage);
        if($page > $totalPages){
            $page = $totalPages;
        }
        if($page < 1){
            $page = 1;
        }

        $data = [];

        $i = 0;
        for($i = ($page - 1) * $this->itemsPerPage; ($i < $page * $this->itemsPerPage && $i < count($this->dataSet)); $i++){
            $data[] = call_user_func_array(array($this->callingInstance, $getDataSet), array($this->dataSet[$i]));
        }
        $itemsInPage = $i - ($page - 1) * $this->itemsPerPage;

        $nextPage = 0;
        $prevPage = 0;

        if($totalPages == 1){
            $nextPage = 1;
            $prevPage = 1;
        }
        else{
            if($page == 1){
                $nextPage = 2;
                $prevPage = $totalPages;
            }
            if($page == $totalPages){
                $nextPage = 1;
                $prevPage = $page - 1;
            }
            if($page != 1 && $page != $totalPages){
                $nextPage = $page +1;
                $prevPage = $page -1;
            }
        }


        return [
            'data' => $data, 
            'itemsPerPage' => $this->itemsPerPage,
            'itemsInPage' => $itemsInPage,
            'prevPage' => $prevPage, 
            'page' => $page, 
            'nextPage' => $nextPage,
            'totalPages' => $totalPages
        ];
    }
}