<?php

namespace App\Http\Controllers;
use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GlobalImportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.globalImport';
    }
    public function showForm()
    {
        return view('data_import.form', $this->data);
    }
    public function importData(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,txt',
            'data_type' => 'required|string',
        ]);

        $file = $request->file('excel_file');
        $dataType = $request->input('data_type');
        set_time_limit(0);

        if ($dataType == 'leads') {
            return $this->Leads($file, $request);
        }  else {
            return redirect()->back()->with('error', 'No Data Type Selected');
        }
    }
    private function Leads($file, $request)
    {
        // new code for enrollment
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize counters and tracking arrays
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $processed_records = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== FALSE) {
                $count = 0;

                while (($all_data = fgetcsv($handle, 5000, ",")) !== FALSE) {
                    if ($count > 0) {
                        if($all_data[0] == ''){
                            continue;
                        }
                        // dd($all_data);
                        //   0 => "2" lead_id
                        //   1 => "Mindstir Co-Working" lead_name
                        //   2 => "info@mindstir.space" lead_email
                        //   3 => "03316611193" lead_phone
                        //   4 => "NULL" numbers 
                        //   5 => "Co-Working Software" subject / description
                        //   6 => "NULL" contact person 
                        //   7 => "NULL" designation
                        //   8 => "7" user_id
                        //   9 => "1"  pipeline_id
                        //   10 => "6"  stage_id
                        //   11 => "NULL"  sources
                        //   12 => "NULL" products
                        //   13 => "NULL" notes
                        //   14 => "NULL" detail
                        //   15 => "NULL" country
                        //   16 => "NULL" labels
                        //   17 => "64" order
                        //   18 => "1" status
                        //   19 => "2" created_by
                        //   20 => "1" is_active
                        //   21 => "0"  is_converted
                        //   22 => "2023-12-18"  date
                        //   23 => "2023-12-18 22:59:14"   created_at
                        //   24 => "2024-11-25 22:51:38"    updated_at
                        $record_status = 'Error';
                        $reason = '';
                        $all_data = array_pad($all_data, 4, '');

                        if (empty($all_data[0]) || empty($all_data[1]) || empty($all_data[2]) || empty($all_data[3])) {
                            $reason = 'Required fields are missing';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => 'Error', 'Reason' => $reason]);
                            continue;
                        }



                        $lead = new Lead();
                        $lead->lead_id = $all_data[0];
                        $lead->user_id = $all_data[1];
                        $lead->client_email = $all_data[2];
                        $lead->mobile = $all_data[3];
                        $lead->cell = $all_data[4];
                        $lead->designation = $all_data[7];
                        $lead->contact_person = $all_data[6];
                        // $lead->pipeline_id = $all_data[9];
                        // $lead->stage_id = $all_data[10];
                        $lead->source_id = $all_data[11];
                        $lead->note = $all_data[25];
                        $lead->country = $all_data[15] ?? null;
                        $lead->created_at = date('Y-m-d', strtotime($all_data[22])).' '.date('H:i:s');
                        $lead->updated_at = date('Y-m-d', strtotime($all_data[22])).' '.date('H:i:s');
                        $lead->save();
                        $deal = new Deal();
                        $deal->name = $all_data[1];
                        $deal->lead_id = $lead->id;
                        $deal->lead_pipeline_id = $all_data[9];
                        $deal->pipeline_stage_id = $all_data[10];
                        //labels
                        $deal->labels = $all_data[16] ?? null;
                        $deal->product = $all_data[12];
                        $deal->subject = $all_data[5];
                        // $deal->agent_id = null;
                        $deal->deal_watcher = $all_data[8];
                        $deal->created_at = date('Y-m-d', strtotime($all_data[22])).' '.date('H:i:s');
                        $deal->updated_at = date('Y-m-d', strtotime($all_data[22])).' '.date('H:i:s');
                        $deal->save();
                        // dd($deal,$lead);
                        // Add to successful records
                        $record_status = 'Success';
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                    } else {
                        // Save the header row with additional columns
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                    }
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'enrollment_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);
                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }
                return redirect()->back()->with('message', "{$success_counter} Enrollment(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e, $all_data);
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }

}
