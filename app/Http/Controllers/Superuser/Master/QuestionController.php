<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\QuestionTable;
use App\Entities\Master\Question;
use App\Exports\Master\QuestionExport;
use App\Exports\Master\QuestionImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\QuestionImport;
use Excel;
use Illuminate\Http\Request;
use Validator;

class QuestionController extends Controller
{
    public function json(Request $request, QuestionTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        return view('superuser.master.question.index');
    }

    public function create()
    {
        return view('superuser.master.question.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'question' => 'required|string',
                'score' => 'required|string',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];
  
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                $question = new Question;

                $question->question = $request->question;
                $question->score = $request->score;

                if ($question->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.question.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        $data['question'] = Question::findOrFail($id);

        return view('superuser.master.question.show', $data);
    }

    public function edit($id)
    {
        $data['question'] = Question::findOrFail($id);

        return view('superuser.master.question.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $question = Question::find($id);

            if ($question == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'question' => 'required|string',
                'score' => 'required|string',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];
  
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                $question->question = $request->question;
                $question->score = $request->score;

                if ($question->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.question.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            $question = Question::find($id);

            if ($question === null) {
                abort(404);
            }

            if ($question->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-question-import-template.xlsx';
        return Excel::download(new QuestionImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            Excel::import(new QuestionImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-question-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new QuestionExport, $filename);
    }
}
