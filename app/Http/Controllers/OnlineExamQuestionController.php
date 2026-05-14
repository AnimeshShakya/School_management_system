<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ClassSchool;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamQuestionAnswer;
use App\Models\OnlineExamQuestionChoice;
use App\Models\OnlineExamQuestionOption;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class OnlineExamQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (! Auth::user()->can('manage-online-exam')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }
        $user = Auth::user();
        if ($user->teacher) {
            $teacher_id = $user->teacher->id;

            // get the class and subject according to subject teacher
            $subject_teacher = SubjectTeacher::where('teacher_id', $teacher_id);
            $class_section_id = $subject_teacher->pluck('class_section_id');
            $class_id = ClassSection::whereIn('id', $class_section_id)->pluck('class_id');
            $subject_id = $subject_teacher->pluck('subject_id');

            $classes = ClassSchool::whereIn('id', $class_id)->with('medium', 'streams')->get();
            $all_subjects = Subject::whereIn('id', $subject_id)->get();
        } else {
            $classes = ClassSchool::with('medium', 'streams')->get();
            $all_subjects = Subject::all();
        }
        return response(view('online_exam.class_questions', compact('classes', 'all_subjects')));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('manage-online-exam')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'class_id' => 'required',
                'subject_id' => 'required',
                'question_type' => 'required|in:0,1',
                'question' => 'required_if:question_type,0',
                'option' => 'required_if:question_type,0|array|min:2',
                'option.*' => 'required_if:question_type,0',
                'equestion' => 'required_if:question_type,1',
                'eoption' => 'required_if:question_type,1|array|min:2',
                'eoption.*' => 'required_if:question_type,1',
                'answer' => 'required|array|size:1',
                'answer.*' => 'required',
                'image' => 'nullable|mimes:jpeg,png,jpg|image|max:3048',
            ],
            [
                'question.required_if' => __('question_is_required'),
                'option.*.required_if' => __('all_options_are_required'),
                'equestion.required_if' => __('question_is_required'),
                'eoption.*.required_if' => __('all_options_are_required'),
                'answer.size' => 'Please select exactly one correct answer.',
            ]
        );

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
            ];

            return response()->json($response);
        }
        try {
            DB::transaction(function () use ($request): void {
                $class_subject_id = ClassSubject::where(['class_id' => $request->class_id, 'subject_id' => $request->subject_id])->pluck('id')->first();

                $question_store = new OnlineExamQuestion;
                $question_store->class_subject_id = $class_subject_id;
                $question_store->question_type = $request->question_type;
                $question_store->question = $request->question_type == 1 ? safe_htmlspecialchars($request->equestion) : safe_htmlspecialchars($request->question);
                $question_store->note = safe_htmlspecialchars($request->note);
                if ($request->hasFile('image')) {
                    $image = $request->file('image');

                    $file_name = time().'-'.$image->getClientOriginalName();
                    $file_path = 'online-exam-questions/'.$file_name;

                    resizeImage($image);

                    $destinationPath = storage_path('app/public/online-exam-questions');
                    $image->move($destinationPath, $file_name);

                    $question_store->image_url = $file_path;
                }
                $question_store->save();

                $requestOptions = $request->question_type == 1 ? $request->eoption : $request->option;

                $options_id = [];
                foreach ($requestOptions as $key => $option) {
                    $question_option_store = new OnlineExamQuestionOption;
                    $question_option_store->question_id = $question_store->id;
                    $question_option_store->option = safe_htmlspecialchars($option);
                    $question_option_store->save();
                    $options_id[$key] = $question_option_store->id;
                }

                $selectedAnswerIndex = $request->answer[0];
                if (! array_key_exists($selectedAnswerIndex, $options_id)) {
                    throw new \RuntimeException('Invalid answer option selected.');
                }

                $question_answer_store = new OnlineExamQuestionAnswer;
                $question_answer_store->question_id = $question_store->id;
                $question_answer_store->answer = $options_id[$selectedAnswerIndex];
                $question_answer_store->save();
            });

            $response = [
                'error' => false,
                'message' => trans('data_store_successfully'),
            ];
        } catch (Throwable $e) {
            $response = [
                'error' => true,
                'message' => trans('error_occurred'),
            ];
        }

        return response()->json($response);
    }

    public function show()
    {
        if (! Auth::user()->can('manage-online-exam')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');

        // get the data of class_subejct_id on the basis of filter of class and subject
        $class_subject_ids = [];
        if (request()->filled('class_id')) {
            $class_subject_ids = ClassSubject::where('class_id', request('class_id'))->pluck('id');
        }
        if (request()->filled('subject_id')) {
            $class_subject_ids = ClassSubject::where('subject_id', request('subject_id'))->pluck('id');
        }
        if (request()->filled('class_id') && request()->filled('subject_id')) {
            $class_subject_ids = ClassSubject::where([
                'class_id' => request('class_id'),
                'subject_id' => request('subject_id'),
            ])->pluck('id');
        }

        if (Auth::user()->hasRole('Teacher')) {
            $teacher_id = Auth::user()->teacher->id;
            $class_section_ids = SubjectTeacher::where('teacher_id', $teacher_id)->pluck('class_section_id');
            $class_ids = ClassSection::whereIn('id', $class_section_ids)->pluck('class_id');
            $class_subject_ids = ClassSubject::whereIn('class_id', $class_ids)->pluck('id');
        }

        $sql = OnlineExamQuestion::with('class_subject', 'options', 'answers')
            // search queries
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orWhere('question', 'LIKE', "%$search%")
                        ->orWhere('created_at', 'LIKE', '%'.date('Y-m-d H:i:s', strtotime($search)).'%')
                        ->orWhere('updated_at', 'LIKE', '%'.date('Y-m-d H:i:s', strtotime($search)).'%')
                        ->orWhereHas('class_subject', function ($q) use ($search) {
                            $q->WhereHas('class', function ($c) use ($search) {
                                $c->where('name', 'LIKE', "%$search%")
                                    ->orWhereHas('medium', function ($m) use ($search) {
                                        $m->where('name', 'LIKE', "%$search%");
                                    });
                            })
                                ->orWhereHas('subject', function ($c) use ($search) {
                                    $c->whereRaw("concat(name,' - ',type) LIKE ?", ["%{$search}%"]);
                                });
                        })
                        ->orWhereHas('options', function ($p) use ($search) {
                            $p->where('option', 'LIKE', "%$search%");
                        });
                });
            });

        // class and subject filter data
        if (! empty($class_subject_ids)) {
            $sql = $sql->whereIn('class_subject_id', $class_subject_ids);
        }
        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();
        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];
        $tempRow = [];
        $no = 1;
        foreach ($res as $row) {
            // data for options which not answers
            $answers_id = '';
            $options_not_answers = '';
            $answers_id = OnlineExamQuestionAnswer::where('question_id', $row->id)->pluck('answer');
            $options_not_answers = OnlineExamQuestionOption::whereNotIn('id', $answers_id)->where('question_id', $row->id)->get();

            $operate = '';
            $operate .= '<a href="#" class="btn btn-xs btn-gradient-primary btn-rounded btn-icon edit-data" data-id='.$row->id.' title="Edit" data-toggle="modal" data-target="#editModal"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;';
            $operate .= '<a href='.route('online-exam-question.destroy', $row->id).' class="btn btn-xs btn-gradient-danger btn-rounded btn-icon delete-question-form" data-id='.$row->id.'><i class="fa fa-trash"></i></a>';

            $tempRow['online_exam_question_id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['class_id'] = $row->class_subject->class_id;
            $tempRow['class_name'] = $row->class_subject->class->name.' - '.$row->class_subject->class->medium->name.' '.($row->class_subject->class->streams->name ?? '');
            $tempRow['class_subject_id'] = $row->class_subject_id;
            $tempRow['subject_id'] = $row->class_subject->subject_id;
            $tempRow['subject_name'] = $row->class_subject->subject->name.' - '.$row->class_subject->subject->type;
            $tempRow['question_type'] = $row->question_type;
            $tempRow['question'] = '';
            $tempRow['options'] = [];
            $tempRow['answers'] = [];
            $tempRow['options_not_answers'] = [];
            if ($row->question_type) {
                $tempRow['question'] = "<div class='equation-editor-inline' contenteditable=false>".safe_htmlspecialchars_decode($row->question).'</div>';
                $tempRow['question_row'] = safe_htmlspecialchars_decode($row->question);

                // options data
                $option_data = [];
                foreach ($row->options as $key => $options) {
                    $option_data = [
                        'id' => $options->id,
                        'option' => "<div class='equation-editor-inline' contenteditable=false>".safe_htmlspecialchars_decode($options->option).'</div>',
                        'option_row' => safe_htmlspecialchars_decode($options->option),
                    ];
                    $tempRow['options'][] = $option_data;
                }

                // answers data
                $answer_data = [];
                foreach ($row->answers as $answers) {
                    $answer_data = [
                        'id' => $answers->id,
                        'option_id' => $answers->answer,
                        'answer' => "<div class='equation-editor-inline' contenteditable=false>".safe_htmlspecialchars_decode($answers->options->option).'</div>',
                    ];
                    $tempRow['answers'][] = $answer_data;
                }

                // options which are not answers
                $no_answers_array = [];
                foreach ($options_not_answers as $no_answers_data) {
                    $no_answers_array = [
                        'id' => $no_answers_data->id,
                    ];
                    $tempRow['options_not_answers'][] = $no_answers_array;
                }
            } else {
                $tempRow['question'] = safe_htmlspecialchars_decode($row->question);

                // options data
                $option_data = [];
                foreach ($row->options as $key => $options) {
                    $option_data = [
                        'id' => $options->id,
                        'option' => safe_htmlspecialchars_decode($options->option),
                    ];
                    $tempRow['options'][] = $option_data;
                }

                // answers data
                $answer_data = [];
                foreach ($row->answers as $key => $answers) {
                    $answer_data = [
                        'id' => $answers->id,
                        'option_id' => $answers->answer,
                        'answer' => safe_htmlspecialchars_decode($answers->options->option),
                    ];
                    $tempRow['answers'][] = $answer_data;
                }

                // options which are not answers
                $no_answers_array = [];
                foreach ($options_not_answers as $no_answers_data) {
                    $no_answers_array = [
                        'id' => $no_answers_data->id,
                    ];
                    $tempRow['options_not_answers'][] = $no_answers_array;
                }
            }
            $tempRow['image'] = $row->image_url;
            $tempRow['note'] = safe_htmlspecialchars_decode($row->note);
            $tempRow['created_at'] = convertDateFormat($row->created_at, 'd-m-Y H:i:s');
            $tempRow['updated_at'] = convertDateFormat($row->updated_at, 'd-m-Y H:i:s');
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (! Auth::user()->can('manage-online-exam')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'edit_class_id' => 'required',
                'edit_subject_id' => 'required',
                'edit_question' => 'required_if:edit_question_type,0',
                'edit_options' => 'required_if:edit_question_type,0|array|min:2',
                'edit_options.*.option' => 'required_if:edit_question_type,0',
                'edit_equestion' => 'required_if:edit_question_type,1',
                'edit_eoption' => 'required_if:edit_question_type,1|array|min:2',
                'edit_eoption.*.option' => 'required_if:edit_question_type,1',
                'edit_answer' => 'required|array|size:1',
                'edit_answer.*' => 'required',
                'edit_image' => 'nullable|mimes:jpeg,png,jpg|image|max:3048',
            ],
            [
                'edit_question.required_if' => __('question_is_required'),
                'edit_options.*.option.required_if' => __('all_options_are_required'),
                'edit_equestion.required_if' => __('question_is_required'),
                'edit_eoption.*.option.required_if' => __('all_options_are_required'),
                'edit_answer.size' => 'Please select exactly one correct answer.',
            ]
        );
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
            ];

            return response()->json($response);
        }
        try {
            DB::transaction(function () use ($request): void {
                if ($request->edit_question_type) {
                    $edit_equestion = OnlineExamQuestion::find($request->edit_id);
                    $class_subject_id = ClassSubject::where(['class_id' => $request->edit_class_id, 'subject_id' => $request->edit_subject_id])->pluck('id')->first();
                    $edit_equestion->class_subject_id = $class_subject_id;
                    $edit_equestion->question = safe_htmlspecialchars($request->edit_equestion);

                    // image code
                    if ($request->hasFile('edit_image')) {
                        if (Storage::disk('public')->exists($edit_equestion->getRawOriginal('image_url'))) {
                            Storage::disk('public')->delete($edit_equestion->getRawOriginal('image_url'));
                        }
                        $image = $request->file('edit_image');

                        // made file name with combination of current time
                        $file_name = time().'-'.$image->getClientOriginalName();

                        // made file path to store in database
                        $file_path = 'online-exam-questions/'.$file_name;

                        // resized image
                        resizeImage($image);

                        // stored image to storage/public/online-exam-questions folder
                        $destinationPath = storage_path('app/public/online-exam-questions');
                        $image->move($destinationPath, $file_name);

                        // saved file path to database
                        $edit_equestion->image_url = $file_path;
                    }
                    $edit_equestion->note = safe_htmlspecialchars($request->edit_note);
                    $edit_equestion->save();

                    $new_options_id = [];
                    foreach ($request->edit_eoption as $key => $edit_option_data) {
                        if ($edit_option_data['id']) {
                            $edit_option = OnlineExamQuestionOption::find($edit_option_data['id']);
                            $edit_option->option = safe_htmlspecialchars($edit_option_data['option']);
                            $edit_option->save();
                        } else {
                            $new_option = new OnlineExamQuestionOption;
                            $new_option->question_id = $request->edit_id;
                            $new_option->option = safe_htmlspecialchars($edit_option_data['option']);
                            $new_option->save();
                            $new_options_id['new'.$key] = $new_option->id;
                        }
                    }

                    $resolvedAnswerId = null;
                    $selectedAnswer = $request->edit_answer[0];
                    if (isset($new_options_id[$selectedAnswer])) {
                        $resolvedAnswerId = $new_options_id[$selectedAnswer];
                    } else {
                        $resolvedAnswerId = $selectedAnswer;
                    }

                    OnlineExamQuestionAnswer::where('question_id', $request->edit_id)->delete();
                    OnlineExamQuestionAnswer::create([
                        'question_id' => $request->edit_id,
                        'answer' => $resolvedAnswerId,
                    ]);
                } else {
                    $edit_question = OnlineExamQuestion::find($request->edit_id);
                    $class_subject_id = ClassSubject::where(['class_id' => $request->edit_class_id, 'subject_id' => $request->edit_subject_id])->pluck('id')->first();
                    $edit_question->class_subject_id = $class_subject_id;
                    $edit_question->question = safe_htmlspecialchars($request->edit_question);

                    // image code
                    if ($request->hasFile('edit_image')) {
                        if (Storage::disk('public')->exists($edit_question->getRawOriginal('image_url'))) {
                            Storage::disk('public')->delete($edit_question->getRawOriginal('image_url'));
                        }
                        $image = $request->file('edit_image');

                        // made file name with combination of current time
                        $file_name = time().'-'.$image->getClientOriginalName();

                        // made file path to store in database
                        $file_path = 'online-exam-questions/'.$file_name;

                        // resized image
                        resizeImage($image);

                        // stored image to storage/public/online-exam-questions folder
                        $destinationPath = storage_path('app/public/online-exam-questions');
                        $image->move($destinationPath, $file_name);

                        // saved file path to database
                        $edit_question->image_url = $file_path;
                    }
                    $edit_question->note = safe_htmlspecialchars($request->edit_note);
                    $edit_question->save();

                    $new_options_id = [];
                    foreach ($request->edit_options as $key => $edit_option_data) {
                        if ($edit_option_data['id']) {
                            $edit_option = OnlineExamQuestionOption::find($edit_option_data['id']);
                            $edit_option->option = safe_htmlspecialchars($edit_option_data['option']);
                            $edit_option->save();
                        } else {
                            $new_option = new OnlineExamQuestionOption;
                            $new_option->question_id = $request->edit_id;
                            $new_option->option = safe_htmlspecialchars($edit_option_data['option']);
                            $new_option->save();
                            $new_options_id['new'.$key] = $new_option->id;
                        }
                    }

                    $resolvedAnswerId = null;
                    $selectedAnswer = $request->edit_answer[0];
                    if (isset($new_options_id[$selectedAnswer])) {
                        $resolvedAnswerId = $new_options_id[$selectedAnswer];
                    } else {
                        $resolvedAnswerId = $selectedAnswer;
                    }

                    OnlineExamQuestionAnswer::where('question_id', $request->edit_id)->delete();
                    OnlineExamQuestionAnswer::create([
                        'question_id' => $request->edit_id,
                        'answer' => $resolvedAnswerId,
                    ]);
                }
            });

            $response = [
                'error' => false,
                'message' => trans('data_update_successfully'),
            ];
        } catch (Throwable $e) {
            $response = [
                'error' => true,
                'message' => trans('error_occurred'),
            ];
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        if (! Auth::user()->can('manage-online-exam')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }
        try {

            // check wheather question is associated with other table..
            $online_exam_choice_questions = OnlineExamQuestionChoice::where('question_id', $id)->count();
            if ($online_exam_choice_questions) {
                $response = [
                    'error' => true,
                    'message' => trans('cannot_delete_beacuse_data_is_associated_with_other_data'),
                ];
            } else {
                OnlineExamQuestion::where('id', $id)->delete();
                $response = [
                    'error' => false,
                    'message' => trans('data_delete_successfully'),
                ];
            }
        } catch (Throwable $e) {
            $response = [
                'error' => true,
                'message' => trans('error_occurred'),
            ];
        }

        return response()->json($response);
    }

    public function removeOptions($id)
    {
        if (! Auth::user()->can('manage-online-exam')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }
        try {
            OnlineExamQuestionOption::where('id', $id)->delete();
            $response = [
                'error' => false,
                'message' => trans('data_delete_successfully'),
            ];
        } catch (Throwable $e) {
            $response = [
                'error' => true,
                'message' => trans('error_occurred'),
            ];
        }

        return response()->json($response);
    }

    public function removeAnswers($id)
    {
        if (! Auth::user()->can('manage-online-exam')) {
            $response = [
                'message' => trans('no_permission_message'),
            ];

            return redirect(route('home'))->withErrors($response);
        }
        try {
            OnlineExamQuestionAnswer::where('id', $id)->delete();
            $response = [
                'error' => false,
                'message' => trans('data_delete_successfully'),
            ];
        } catch (Throwable $e) {
            $response = [
                'error' => true,
                'message' => trans('error_occurred'),
            ];
        }

        return response()->json($response);
    }
}
