<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InsertController extends Controller
{
    public function index()
    {
        return view('insert');
    }
//------------------------------------------------------------------------------------------------------------
    public function insert(Request $request)
    {
        $validate = $request->validate([
            'file' => 'required'
        ],);
//------------------------------------------------------------------------------------------------------------        
        if($validate == true){
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $filetype = $file->extension();
            Storage::disk('local')->put($filename, file_get_contents($file));
            $inputfilename = Storage::path($filename);
            $testAgainstFormats = [
                IOFactory::READER_XLSX,
                IOFactory::READER_XLS,
                IOFactory::READER_CSV
            ];
            try {
                $inputfiletype = IOFactory::identify($inputfilename, $testAgainstFormats);
            } catch (Exception $e) {
                $e->getMessage();
            }
            $reader = IOFactory::createReader($inputfiletype);
            $spreadsheet = $reader->load($inputfilename);
            $worksheet = $spreadsheet->getActiveSheet();
//------------------------------------------------------------------------------------------------------------
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
//------------------------------------------------------------------------------------------------------------
            $tablecolumn = [];
            for($row = 1; $row <= 1; ++$row){
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $value = $worksheet->getCell([$col, $row])->getValue();
                    array_push($tablecolumn, $value);
                }
            }
            if($tablecolumn[0] == null){
                return redirect()->back()->withErrors(['errorMsg' => 'Top row is empty on your submitted file']);
            }
//------------------------------------------------------------------------------------------------------------
            $tablevalue = [];
            for($row = 2; $row <= $highestRow; ++$row){
                $tablerow = [];
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCell([$col, $row]);
                    $cellvalue = $worksheet->getCell([$col, $row])->getValue();
                    if(Date::isDateTime($cell, $cellvalue) == true){
                        $timestamp = Date::excelToTimestamp($cellvalue);
                        $value = date('d-M-Y', $timestamp);
                    }else{
                        $value = $cellvalue;
                    } 
                    array_push($tablerow, $value);
                }
                array_push($tablevalue, $tablerow);
            }
//------------------------------------------------------------------------------------------------------------
            $newtablevalue = [];
            foreach($tablevalue as $tv){
                $modifyarray = [];
                $modify = null;
                foreach($tv as $t){
                    if (gettype($t) == 'string'){
                        $modify = "'" . $t . "'";
                    }else{
                        $modify = $t;
                    }
                    array_push($modifyarray, $modify);
                }
                array_push($newtablevalue, array_replace($tv, $modifyarray));
            }
//------------------------------------------------------------------------------------------------------------
            $query = null;
            $filenamewithoutext = basename($inputfilename, "." . $filetype);
            $newfilename = "query.sql";
            foreach($newtablevalue as $tv){
                $query .= "INSERT INTO " . $filenamewithoutext . " ( " . implode(", ", $tablecolumn) . " ) VALUES ( " . implode(", ", $tv) . " ); \n";
            }
            Storage::disk('local')->put($newfilename, $query);
            Storage::disk('local')->delete($filename);
            return redirect('/insert');
        }
    }
//------------------------------------------------------------------------------------------------------------
    public function download()
    {
        if(file_exists(storage_path() . "\app\query.sql")){
            return response()->download(storage_path('app/query.sql'))->deleteFileAfterSend(true);
        }else{
            return redirect('/insert');
        }
    }
}
