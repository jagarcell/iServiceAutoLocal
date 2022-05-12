<?php

namespace App\Service;

class SqlQueryBuilder {

    private $table;
    private $filtersAndSorts;
    private $columnNames;

    public function __construct($table, $filtersAndSorts, $columnNames)
    {
        $this->table = $table;
        $this->filtersAndSorts = $filtersAndSorts;
        $this->columnNames = $columnNames;
    }

    public function getSelectStatement()
    {
        return $this->buildSelectString();
    }

    private function buildSelectString()
    {
        $sql = '
            SELECT * FROM ' . $this->table . ' t' .
            $this->filterSql($this->filtersAndSorts) .
            $this->searchSql($this->filtersAndSorts, $this->columnNames) .
            $this->sortSql($this->filtersAndSorts);
        
        return $sql;
    }

    /**
     * @method ($filtersAndSorts {
     *                      filter:{
     *                          columnName:value, // for an exact match
     *                          columnName: {
     *                              min:value,
     *                              max:value
     *                          }                 // for a range
     *                       },
     *                      sort: {
     *                          columnName:direction (ASC or DESC)
     *                      }
     *                    })
     * 
     * @return (The sql string to be appended to the sql root string)
     */
    public function filterSql($filtersAndSorts)
    {
        $filterSqlStr = "";
        if(isset($filtersAndSorts['filter']) && count($filtersAndSorts['filter']) > 0){
            $filters = $filtersAndSorts['filter'];
            $filterSqlStr .= " WHERE ";
            $columnsAnd = "";
            foreach ($filters as $columnName => $filter) {
                $filterSqlStr .= $columnsAnd;
                $columnName = "t." . $columnName; 
                $rangeAnd = "";
                if(isset($filter['min']) && isset($filter['max'])){
                    $rangeAnd = " AND ";
                }
                if(isset($filter['min'])){
                    $filterSqlStr .=  $columnName . " >= " . '"' . $filter['min'] . '"' . $rangeAnd;
                }
                if(isset($filter['max'])){
                    $filterSqlStr .= $columnName . " <= " . '"' . $filter['max'] . '"';
                }
                if(!isset($filter['min']) && !isset($filter['max'])){
                    $filterSqlStr .= $columnName . " = " . '"' . $filter . '"';
                }
                $columnsAnd = " AND ";
            }
        }
        return $filterSqlStr;
    }

    /**
     * @method ($filtersAndSorts {
     *                      filter:{
     *                          columnName:value, // for an exact match
     *                          columnName: {
     *                              min:value,
     *                              max:value
     *                          }                 // for a range
     *                       },
     *                      sort: {
     *                          columnName:direction (ASC or DESC)
     *                      }
     *                    })
     * 
     * @return (The sql string to be appended to the sql root string)
     */

    public function sortSql($filtersAndSorts)
    {
        $sortSqlStr = "";
        if(isset($filtersAndSorts['sort']) && count($filtersAndSorts['sort']) > 0){
            $sorts = $filtersAndSorts['sort'];
            $sortSqlStr = " ORDER BY ";
            $columnsComma = "";
            foreach ($sorts as $columnName => $direction) {
                $sortSqlStr .= $columnsComma;
                $columnName = "t." . $columnName;
                $sortSqlStr .= $columnName . " " . $direction;
                $columnsComma = ", ";
            }
        }
        return $sortSqlStr;
    }

    /**
     * @method $filtersAndSorts {
     *                      filter:{
     *                          columnName:value, // for an exact match
     *                          columnName: {
     *                              min:value,
     *                              max:value
     *                          }                 // for a range
     *                       },
     *                      sort: {
     *                          columnName:direction (ASC or DESC)
     *                      }
     *                    })
     *          The names of the columns to be included in the search
     *         $columnNames [
     *                          'column 1',
     *                          'column 2',
     *                          'column n
     *                      ]
     * 
     * @return (The sql string to be appended to the sql root string)
     */

    public function searchSql($filtersAndSorts, $columnNames)
    {
        $searchSql = "";
        $columnsOr = "";
        if(isset($filtersAndSorts['searchText']) && count($columnNames) > 0){
            $searchSql = " AND (";

            $searchText = $filtersAndSorts['searchText'];
            $keywords = \explode(" ", $searchText);
            foreach ($columnNames as $key => $columnName) {
                foreach ($keywords as $key => $keyword) {
                    $keyword = \trim($keyword);
                    if(empty($keyword)){
                        continue;
                    }
                    $searchSql .= $columnsOr . "t." . $columnName . " like '%" . $keyword . "%'";
                    $columnsOr = " OR ";
                }
            }
            $searchSql .= ")";
        }
        return $searchSql;
    }
}