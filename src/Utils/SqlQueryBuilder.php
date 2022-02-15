<?php

namespace App\Utils;

class SqlQueryBuilder {

    /**
     * @method ($criteria {
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
    public function filterSql($criteria)
    {
        $filterSqlStr = "";
        if(isset($criteria['filter']) && count($criteria['filter']) > 0){
            $filters = $criteria['filter'];
            $filterSqlStr .= " WHERE ";
            $columnsAnd = "";
            foreach ($filters as $columnName => $filter) {
                $filterSqlStr .= $columnsAnd;
                $columnName = "v." . $columnName; 
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
     * @method ($criteria {
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

    public function sortSql($criteria)
    {
        $sortSqlStr = "";
        if(isset($criteria['sort']) && count($criteria['sort']) > 0){
            $sorts = $criteria['sort'];
            $sortSqlStr = " ORDER BY ";
            $columnsComma = "";
            foreach ($sorts as $columnName => $direction) {
                $sortSqlStr .= $columnsComma;
                $columnName = "v." . $columnName;
                $sortSqlStr .= $columnName . " " . $direction;
                $columnsComma = ", ";
            }
        }
        return $sortSqlStr;
    }

    public function searchSql($criteria, $columnNames)
    {
        $searchSql = "";
        $columnsOr = "";
        if(isset($criteria['searchText']) && count($columnNames) > 0){
            $searchSql = " AND (";

            $searchText = $criteria['searchText'];
            $keywords = \explode(" ", $searchText);
            foreach ($columnNames as $key => $columnName) {
                foreach ($keywords as $key => $keyword) {
                    $keyword = \trim($keyword);
                    if(empty($keyword)){
                        continue;
                    }
                    $searchSql .= $columnsOr . "v." . $columnName . " like '%" . $keyword . "%'";
                    $columnsOr = " OR ";
                }
            }
            $searchSql .= ")";
        }
        return $searchSql;
    }
}