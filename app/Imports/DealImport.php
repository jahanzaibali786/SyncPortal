<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class DealImport implements ToArray
{
    public static function fields(): array
    {
        return [
            ['id' => 'lead_contact_email', 'name' => __('modules.deal.leadContactEmail'), 'required' => 'Conditional'],
            ['id' => 'deal_name', 'name' => __('modules.deal.dealName'), 'required' => 'Yes'],
            ['id' => 'client_name', 'name' => __('modules.deal.client_name'), 'required' => 'Yes'],
            ['id' => 'phone_number', 'name' => __('modules.deal.dealMobile'), 'required' => 'Conditional'],
            ['id' => 'designation', 'name' => __('modules.deal.dealDesignation'), 'required' => 'No'],
            ['id' => 'pipeline', 'name' => __('modules.deal.pipeline'), 'required' => 'Yes'],
            ['id' => 'deal_stage', 'name' => __('modules.deal.stages'), 'required' => 'Yes'],
            ['id' => 'value', 'name' => __('modules.deal.dealValue'), 'required' => 'No'],
            ['id' => 'close_date', 'name' => __('modules.deal.closeDate'), 'required' => 'No'],
        ];
    }

    /**
     * @param array $rows  Raw rows returned by Excel::toArray()
     * @return array       Normalized associative rows keyed by field id
     * @throws ValidationException
     */
    public function array(array $rows): array
    {
        $fields = self::fields();
        $fieldIds = array_column($fields, 'id');
        $fieldNames = array_column($fields, 'name');

        $mappedRows = [];

        foreach ($rows as $index => $row) {
            // Decide if row is associative (headers present) or numeric
            $isAssoc = array_keys($row) !== range(0, count($row) - 1);

            $assoc = [];

            if ($isAssoc) {
                // Keys might be field IDs already, or header names; try both
                $rowKeys = array_keys($row);
                $keysIntersect = array_intersect($rowKeys, $fieldIds);

                if (count($keysIntersect) > 0) {
                    // Keys include field ids — use them directly but ensure all fields exist
                    foreach ($fieldIds as $fid) {
                        $assoc[$fid] = array_key_exists($fid, $row) ? $row[$fid] : null;
                    }
                } else {
                    // Try to match header names (normalize header and field names)
                    $normalizedFieldMap = [];
                    foreach ($fields as $f) {
                        $key = strtolower(preg_replace('/\W+/', '', $f['name']));
                        $normalizedFieldMap[$key] = $f['id'];
                    }

                    foreach ($row as $k => $v) {
                        $norm = strtolower(preg_replace('/\W+/', '', $k));
                        if (isset($normalizedFieldMap[$norm])) {
                            $assoc[$normalizedFieldMap[$norm]] = $v;
                        }
                    }

                    // Ensure every expected field key exists (fill with nulls)
                    foreach ($fieldIds as $fid) {
                        if (!array_key_exists($fid, $assoc)) {
                            $assoc[$fid] = null;
                        }
                    }
                }
            } else {
                // Numeric-indexed row: map by column order of fields()
                foreach ($fieldIds as $i => $fid) {
                    $assoc[$fid] = $row[$i] ?? null;
                }
            }

            // Validate mapped row
            $validator = Validator::make($assoc, [
                'lead_contact_email'  => 'nullable|email|required_without:phone_number',
                'phone_number'        => 'nullable|string|required_without:lead_contact_email',
                'deal_name'           => 'required|string',
                'client_name'         => 'required|string',
                'pipeline'            => 'required|string',
                'deal_stage'          => 'required|string',
                'value'               => 'required|numeric',
                'close_date'          => 'required|date',
            ]);

            if ($validator->fails()) {
                // Throw with row number (1-indexed for humans)
                throw ValidationException::withMessages([
                    'row_' . ($index + 1) => $validator->errors()->all(),
                ]);
            }
            $mappedRows[] = $assoc;
        }
        return $mappedRows;
    }
}
