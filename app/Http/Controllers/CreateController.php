<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class CreateController extends Controller
{
    public function index()
    {
        // echo "<pre>";
        // print_r(session()->all());
        return view('create');
    }
//-------------------------------------------------------------------------------------------------------------
    public function create(Request $request)
    {
        $validate = $request->validate([
            'file' => 'required'
        ],);

        if($validate == true){
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $filetype = $file->extension();
            $onlyfilename = explode(".", $filename);
            $convertedfilename = "oxcel." . $filetype;
            Storage::disk('local')->put($convertedfilename, file_get_contents($file));
            $inputfilename = Storage::path($convertedfilename);
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

            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

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
            session()->put(['tablecolumn' => $tablecolumn, 'filename' => $onlyfilename[0]]);
            return redirect('/create-query');
        }
    }
//-----------------------------------------------------------------------------------------------------------
    public function viewCreateQuery(){
        return view('create-query');
    }
//-----------------------------------------------------------------------------------------------------------
    public function createQuery(Request $request)
    {
        $filename = $request->get('table_name');
        $columnname = $request->get('column_name');
        $datatype = $request->get('data_type');
        $size = $request->get('size');
        if($datatype[0] == null){
            return redirect('/create-query')->withErrors(['errorMsg' => 'data typy needed']);
        }
        $newcombinearray = [];
        for($i = 0; $i < count($columnname); $i++){
            $a = null;
            $b = null;
            $c = null;
            for($j = $i; $j <= $i; $j++){
                $a .= $columnname[$j];
            }
            for($j = $i; $j <= $i; $j++){
                $b .= $datatype[$j];
            }
            for($j = $i; $j <= $i; $j++){
                if($size[$j] != ""){
                    $c .= "(" . $size[$j] . ")";
                }
            }
            array_push($newcombinearray ,$a . " " . $b . $c);
        }
        $query = "CREATE TABLE " . $filename . "(\n" . implode(",\n", $newcombinearray) . "\n)";
        echo $query;
        Storage::disk('local')->put("query.sql", $query);
        Storage::disk('local')->delete("oxcel.xlsx");
        session()->flush();
        return redirect('/');
    }
//------------------------------------------------------------------------------------------------------------
    public function download()
    {
        if(file_exists(storage_path() . "\app\query.sql")){
            return response()->download(storage_path('app/query.sql'))->deleteFileAfterSend(true);
        }else{
            return redirect('/');
        }
        
    }
}
