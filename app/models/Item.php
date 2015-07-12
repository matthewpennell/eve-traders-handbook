<?php

class Item extends Eloquent {

    protected $table = 'items';

    public function kill()
    {
        return $this->hasOne('Kill', 'killID');
    }

    public function type()
    {
        return $this->belongsTo('Type', 'typeID');
    }

    public static function selectedItems($page = 1, $whereraw = NULL, $per_page)
    {
        if ($whereraw != NULL)
        {
            return DB::table('items')
                ->leftJoin('profits', 'items.typeID', '=', 'profits.typeID')
                ->select('items.typeID', 'typeName', 'categoryName', 'metaGroupName', 'allowManufacture', 'manufactureCost', 'profitIndustry', 'profitImport', DB::raw('SUM(qty) AS qty'))
                ->whereRaw('WHERE (' . implode(') and (', $whereraw) . ')')
                ->groupBy('typeID')
                ->orderBy('qty', 'desc')
                ->skip(($page - 1) * $per_page)
                ->take($per_page)
                ->get();
        }
        else {
            return DB::table('items')
                ->leftJoin('profits', 'items.typeID', '=', 'profits.typeID')
                ->select('items.typeID', 'typeName', 'categoryName', 'metaGroupName', 'allowManufacture', 'manufactureCost', 'profitIndustry', 'profitImport', DB::raw('SUM(qty) AS qty'))
                ->groupBy('typeID')
                ->orderBy('qty', 'desc')
                ->skip(($page - 1) * $per_page)
                ->take($per_page)
                ->get();
        }
    }

    public static function getRowCount($whereraw = NULL)
    {
        $whererawsql = '';
        if ($whereraw != NULL)
        {
            $whererawsql = 'WHERE (' . implode(') and (', $whereraw) . ')';
        }
        $query = DB::select(DB::raw('SELECT COUNT(*) AS count FROM (SELECT typeID, SUM(qty) FROM items ' . $whererawsql . ' GROUP BY typeID) AS custom'));
        return $query[0]->count;
    }

}
