<?php

namespace Bread\Http\Controllers;

use Bread\BreadFacade;
use Bread\Models\BreadRow;
use Illuminate\Http\Request;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Facades\Voyager;

class BreadManagerController extends Controller
{
    public function index()
    {
        $breads = BreadFacade::model('Bread')->orderBy('table_name')->get();
        $bread_names = $breads->pluck('table_name')->toArray();
        $tables = array_diff(SchemaManager::listTableNames(), $bread_names, config('voyager.database.tables.hidden', []));

        return view('bread::manager.index', compact('breads', 'tables'));
    }

    //Create BREAD
    public function create($table)
    {
        return view('bread::manager.edit-add-bread', compact('table'));
    }

    //Store created or updateds BREAD
    public function store(Request $request)
    {
        $bread = BreadFacade::model('Bread')->updateOrCreate([
            'table_name' => $request->table_name,
        ], $request->except('_token', 'view_role'));

        //Create sync value
        if ($request->has('view_role')) {
            foreach ($request->view_role as $view_id => $view_role) {
                $view = BreadFacade::model('BreadView')->find($view_id);
                $view->roles()->detach();
                foreach ($view_role as $relation) {
                    list($role_id, $action) = explode('_', $relation);
                    $view->roles()->attach($role_id, ['action' => $action]);
                }
            }
        }

        return redirect(route('voyager.bread-hook.edit', $bread->table_name));
    }

    //Edit BREAD
    public function edit($name)
    {
        $bread = BreadFacade::model('Bread')->whereTableName($name)->first();

        return view('bread::manager.edit-add-bread', compact('bread'));
    }

    //Delete BREAD
    public function destroy(Request $request, $bread)
    {
        $bread = BreadFacade::model('Bread')->find($bread);
        foreach ($bread->views as $view) {
            $view->rows()->delete();
            $view->delete();
        }

        $bread->delete();

        return redirect(route('voyager.bread-hook.index'));
    }

    public function storeView(Request $request)
    {
        $arg = '';
        if ($request->has('create_default') && $request->create_default == 'true') {
            $arg = '?default=true';
        }

        $view = BreadFacade::model('BreadView')->create($request->except('_token', 'create_default'));

        return redirect(route('voyager.bread-hook.edit.view', $view).$arg);
    }

    public function editView(Request $request, $view)
    {
        $view = BreadFacade::model('BreadView')->find($view);
        $columns = get_model_fields($view->bread->model);

        $rows = [];
        if ($request->has('default') && $request->default) {
            //Fake rows based on table
            $table_details = \TCG\Voyager\Database\Schema\SchemaManager::describeTable($view->bread->model->getTable());
            foreach ($table_details as $key => $column) {
                $row = new BreadRow();
                if ($column['type'] == 'integer') {
                    $row->type = 'number';
                } elseif ($column['type'] == 'text') {
                    $row->type = 'text_area';
                } else {
                    $row->type = 'text';
                }
                $row->field = $column['name'];
                $row->order = $key;

                if ($view->view_type == 'list') {
                    $row->options = [
                        'label'      => title_case(str_replace(['_', '-'], ' ', $row->field)),
                        'searchable' => true,
                        'orderable'  => true,
                    ];
                } else {
                    $row->options = ['label' => title_case(str_replace(['_', '-'], ' ', $row->field))];
                }
                $rows[] = $row;
            }
        } else {
            $rows = $view->rows;
        }

        return view('bread::manager.edit-'.$view->view_type, compact('view', 'columns', 'rows'));
    }

    public function updateView(Request $request)
    {
        //Get BREAD
        $bread = BreadFacade::model('Bread')->where('table_name', $request->bread_name)->firstOrFail();

        $persistent_ids = [];
        if (is_array($request->input('row'))) {
            foreach ($request->input('row') as $order => $row) {
                if (isset($row['validation']) && isset($row['validation']['rules']) && isset($row['validation']['rules'][0]['rule'])) {
                    $validation = parse_validation($row['validation']['rules']);
                } else {
                    $validation = null;
                }

                $newrow = BreadFacade::model('BreadRow')->updateOrCreate([
                    'id' => $row['id'],
                ], [
                    'bread_view_id'   => $request->view_id,
                    'field'     	     => (str_contains($row['formfield'], 'relationship') ? 'relationship' : $row['column']),
                    'type'      	     => $row['formfield'],
                    'width'      	    => (isset($row['width']) ? $row['width'] : null),
                    'order'			        => $order,
                    'options'		       => (isset($row['options']) ? $row['options'] : null),
                    'validation_rules'=> $validation,
                ]);

                $persistent_ids[] = $newrow->id;
            }
        }
        BreadFacade::model('BreadRow')->where('bread_view_id', $request->view_id)
                                      ->whereNotIn('id', $persistent_ids)
                                      ->delete();

        return redirect(route('voyager.bread-hook.edit.view', $request->view_id));
    }

    public function deleteView(Request $request, $view)
    {
        $view = BreadFacade::model('BreadView')->find($view);
        $bread = $view->bread;
        $view->rows()->delete();
        $view->delete();

        return redirect(route('voyager.bread-hook.edit', $bread->table_name));
    }

    public function renderFormfield(Request $request)
    {
        $row = BreadFacade::model('BreadRow')->where('field', $request->field)->first(); /* @todo: this is NOT unique **/
        if ($request->has('type')) {
            $type = $request->type;

            if ($type == 'input') {
                return BreadFacade::formField($row->type)->createInput(null, array_merge($row->options, $request->options), $request->name);
            } else {
                /* @todo: Add other things here. Currently only input is needed **/
            }
        }
    }
}
