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

    public static function selectedItems($page = 1, $whereraw = NULL)
    {
        return DB::table('items')
            ->leftJoin('profits', 'items.typeID', '=', 'profits.typeID')
            ->select('items.typeID', 'typeName', 'categoryName', 'metaGroupName', 'allowManufacture', 'profitIndustry', 'profitImport', DB::raw('SUM(qty) AS qty'))
            ->whereRaw('(' . implode(') and (', $whereraw) . ')')
            ->orderBy('qty', 'desc')
            ->groupBy('typeID')
            ->skip(($page - 1) * 20)
            ->take(20)
            ->get();
    }

    public static function getRowCount($whereraw = NULL)
    {
        $query = DB::select(DB::raw('SELECT COUNT(*) AS count FROM (SELECT typeID, SUM(qty) FROM items WHERE (' . implode(') and (', $whereraw) . ') GROUP BY typeID) AS custom'));
        return $query[0]->count;
    }

}
