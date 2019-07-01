<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use \Illuminate\Http\Request;
use App\Transformers\Json;
use Carbon\Carbon;

use App\Checklist;
use App\Item;
use App\History;
use App\Template;

class CheklistController extends BaseController
{
    public function index(Request $request)
    {
    	$checklist = Checklist::query();

    	if ($request->has('include')) {
            $include = explode(',', $request->include);

            try {
                $checklist = $checklist->with($include); 
            } catch (\Illuminate\Database\Eloquent\RelationNotFoundException $e) {
                return Json::exception('Error relation');
            }
        }

        if($request->has('sort')){
            $sorts = explode(',', $request->sort);
            foreach ($sorts as $sort) {
                $field = preg_replace('/[-]/', '', $sort);
                if (preg_match('/^[-]/', $sort)) {
                    $checklist->orderBy($field, 'desc');
                } else {
                    $checklist->orderBy($field, 'asc');
                }
            }
        }

        if($request->has('filter')) {
        	foreach ($request->filter as $key => $filter) {
        		foreach ($filter as $k => $value) {
        			$value = explode(',',$value);
        			if($k == 'is') {
        				$checklist->where($key,$value);
        			} elseif ($k == '!is') {
        				$checklist->where($key,'!=',$value);
        			} elseif ($k == 'in') {
        				$checklist->whereIn($key,$value);
        			} elseif ($k == '!in') {
        				$checklist->whereNotIn($key,$value);
        			} elseif ($k == 'like') {
        				$checklist->where($key,'like',$value.'%');
        			} elseif ($k == '!likw') {
        				$checklist->where($key,'not like',$value.'%');
        			}
        		}
        	}
        }

        $checklist = $checklist->paginate($request->input('offset', 10))->appends($request->all());

        if($request->has('field')) {
	        $fields = $request->field;
			$arrayField = explode(',', $fields);

			foreach($checklist as $dt){
				foreach($arrayField as $field){
					$dt->makeVisible($field);
				}
			}
        }

        return json::response($checklist,$request);
    }

    public function show(Request $request, $id)
    {
    	$checklist = Checklist::query();


        if ($request->has('include')) {
            $include = explode(',', $request->include);

            try {
                $checklist = $checklist->with($include); 
            } catch (\Illuminate\Database\Eloquent\RelationNotFoundException $e) {
                return Json::exception('Error relation');
            }
        }

        $checklist = $checklist->findOrFail($id);

    	if($request->has('field')) {
	        $fields = $request->field;
			$arrayField = explode(',', $fields);

			foreach($arrayField as $field){
				$checklist->makeVisible($field);
			}
        }

        return json::response($checklist,$request);
    }

    public function store(Request $request)
    {

    	$checklist = new Checklist;
    	$checklist->object_domain = $request->object_domain;
    	$checklist->object_id = $request->object_id;
    	$checklist->due = $request->due;
    	$checklist->description = $request->description;
    	$checklist->items = $request->items;
    	$checklist->task_id = $request->task_id;
    	$checklist->is_completed = false;
    	$checklist->urgency = 2;
    	$checklist->created_by = $request->user()->id;
    	$checklist->save();

    	$data['loggable_type'] = 'checklist';
    	$data['loggable_id'] = $checklist->id;
    	$data['action'] = 'store';
    	$data['kwuid'] = $request->user()->id;
    	$data['value'] = $request->all();

    	$this->history($data);

        return json::response($checklist,$request);
    }

    public function update(Request $request, $id)
    {
    	$checklist = Checklist::findOrFail($id);
    	$checklist->object_domain = $request->object_domain;
    	$checklist->object_id = $request->object_id;
    	$checklist->description = $request->description;
    	$checklist->is_completed = $request->is_completed;
    	$checklist->updated_by = $request->user()->id;
    	$checklist->save();

    	$data['loggable_type'] = 'checklist';
    	$data['loggable_id'] = $id;
    	$data['action'] = 'update';
    	$data['kwuid'] = $request->user()->id;
    	$data['value'] = $request->all();

    	$this->history($data);

        return json::response($checklist,$request);
    }

    public function delete(Request $request, $id)
    {
    	$checklist = Checklist::findOrFail($id);
    	$checklist->delete();

    	$data['loggable_type'] = 'checklist';
    	$data['loggable_id'] = $id;
    	$data['action'] = 'delete';
    	$data['kwuid'] = $request->user()->id;
    	$data['value'] = $request->all();

    	$this->history($data);

        return json::response($checklist,$request);
    }

    public function checklistItemById(Request $request, $id)
    {

        $checklist = Checklist::where('id',$id)->with('items');

        if($request->has('sort')){
            $sorts = explode(',', $request->sort);
            foreach ($sorts as $sort) {
                $field = preg_replace('/[-]/', '', $sort);
                if (preg_match('/^[-]/', $sort)) {
                    $checklist->orderBy($field, 'desc');
                } else {
                    $checklist->orderBy($field, 'asc');
                }
            }
        }

        if($request->has('filter')) {
        	foreach ($request->filter as $key => $filter) {
        		foreach ($filter as $k => $value) {
        			$value = explode(',',$value);
        			if($k == 'is') {
        				$checklist->where($key,$value);
        			} elseif ($k == '!is') {
        				$checklist->where($key,'!=',$value);
        			} elseif ($k == 'in') {
        				$checklist->whereIn($key,$value);
        			} elseif ($k == '!in') {
        				$checklist->whereNotIn($key,$value);
        			} elseif ($k == 'like') {
        				$checklist->where($key,'like',$value.'%');
        			} elseif ($k == '!likw') {
        				$checklist->where($key,'not like',$value.'%');
        			}
        		}
        	}
        }

        $checklist = $checklist->first();

        if($request->has('field')) {
	        $fields = $request->field;
			$arrayField = explode(',', $fields);

			foreach($arrayField as $field){
				$checklist->makeVisible($field);
			}
        }

        return json::response($checklist,$request);
    }

    public function ItemById(Request $request, $id, $itemId)
    {
    	$checklist = Checklist::where('id',$id)->with(['items' => function ($query) use ($itemId){
									    $query->where('id', $itemId);
									}])->first();

    	return json::response($checklist,$request);
    }

    public function completeItem(Request $request)
    {

    	foreach ($request->data as $id) {
    		$item = Item::findOrFail($id['item_id']);
    		$item->is_completed = true;
    		$item->save();

    		$data['loggable_type'] = 'items';
	    	$data['loggable_id'] = $item->id;
	    	$data['action'] = 'update';
	    	$data['kwuid'] = $request->user()->id;
	    	$data['value'] = $id['item_id'];

	    	$this->history($data);
    	}

    	return json::response($item,$request);
    }

    public function incompleteItem(Request $request)
    {

    	foreach ($request->data as $id) {
    		$item = Item::findOrFail($id['item_id']);
    		$item->is_completed = false;
    		$item->save();

    		$data['loggable_type'] = 'items';
	    	$data['loggable_id'] = $item->id;
	    	$data['action'] = 'update';
	    	$data['kwuid'] = $request->user()->id;
	    	$data['value'] = $id['item_id'];

	    	$this->history($data);
    	}

    	return json::response($item,$request);
    }

    public function storeItem(Request $request, $id)
    {
    	$item = new Item;
    	$item->name = $request->name;
    	$item->user_id = 0;
    	$item->due = $request->due;
    	$item->urgency = $request->urgency;
    	$item->description = $request->description;
    	$item->assignee_id = $request->assignee_id;
    	$item->checklist_id = $id;
    	$item->created_by = $request->user()->id;
    	$item->updated_by = $request->user()->id;
    	$item->save();

    	$data['loggable_type'] = 'items';
    	$data['loggable_id'] = $item->id;
    	$data['action'] = 'store';
    	$data['kwuid'] = $request->user()->id;
    	$data['value'] = $request->all();

    	$this->history($data);

    	return json::response($item,$request);
    }

    public function bulkUpdateItem(Request $request)
    {

    	foreach ($request->data as $key => $data) {
    		$response[$key]['id'] = $data['id'];
    		$response[$key]['action'] = 'update';
    		foreach ($data['attributes'] as $field => $value) {
    			$item = Item::findOrFail($data['id']);
    			$item->$field = $value;
    			
    			if($item->save()) {
    				$response[$key]['status'] = 200;

    				$data['loggable_type'] = 'items';
			    	$data['loggable_id'] = $item->id;
			    	$data['action'] = 'update';
			    	$data['kwuid'] = $request->user()->id;
			    	$data['value'] = $value;

			    	$this->history($data);
    			}
    		}
    	}

    	return json::response($response,$request);
    }

    public function updateItem(Request $request, $id, $itemId)
    {
    	$item = Item::where('id',$itemId)->where('checklist_id',$id)->first();

    	foreach ($request->data as $field => $data) {
    		foreach ($data as $field => $value) {
    			$item->$field = $value;
    			$item->save();

    			$data['loggable_type'] = 'items';
		    	$data['loggable_id'] = $item->id;
		    	$data['action'] = 'update';
		    	$data['kwuid'] = $request->user()->id;
		    	$data['value'] = $value;

		    	$this->history($data);
    		}
    	}

    	return json::response($item,$request);
    }

    public function deleteItem(Request $request, $id, $itemId)
    {
    	$item = Item::where('id',$itemId)->where('checklist_id',$id)->first();
    	$item->delete();

    	$data['loggable_type'] = 'items';
    	$data['loggable_id'] = $item->id;
    	$data['action'] = 'delete';
    	$data['kwuid'] = $request->user()->id;
    	$data['value'] = $request->all();

    	$this->history($data);

    	return json::response($item, $request);
    }

    public function summaryItem(Request $request)
    {

    	$date = Carbon::now()->toDateString();
    	$start_week = Carbon::now()->startOfWeek()->toDateString();
    	$end_week = Carbon::now()->endOfWeek()->toDateString();

    	$start_month = Carbon::now()->startOfMonth()->toDateString();
    	$end_month = Carbon::now()->endOfMonth()->toDateString();

    	$data['today'] = Item::whereDate('created_at',$date)->count();
    	$data['past_due'] = Item::whereDate('due','<',$date)->count();
    	$data['this_week'] = Item::whereBetween('created_at',[$start_week,$end_week])->count();
    	$data['past_week'] = Item::whereDate('created_at','<',$start_week)->count();
    	$data['this_month'] = Item::whereDate('created_at','>=',$start_month)->count();
    	$data['past_month'] = Item::whereDate('created_at','<',$start_month)->count();
    	$data['total'] = $data['this_month'];

    	return json::response($data,$request);
    }

    public function storeTemplate(Request $request)
    {
    	foreach ($request->data as $key => $value) {
	    	$temp = new Template;
	    	$temp->name = $value['name'];
	    	$temp->checklist = json_encode($value['checklist']);
	    	$temp->items = json_encode($value['items']);
	    	$temp->save();

	    	$data['loggable_type'] = 'template';
	    	$data['loggable_id'] = $temp->id;
	    	$data['action'] = 'store';
	    	$data['kwuid'] = $request->user()->id;
	    	$data['value'] = $value;

	    	$this->history($data);
    	}

    	return json::response($temp,$request);
    }

    public function indexTemplate(Request $request)
    {
    	$template = Template::query();

    	if($request->has('sort')){
            $sorts = explode(',', $request->sort);
            foreach ($sorts as $sort) {
                $field = preg_replace('/[-]/', '', $sort);
                if (preg_match('/^[-]/', $sort)) {
                    $template->orderBy($field, 'desc');
                } else {
                    $template->orderBy($field, 'asc');
                }
            }
        }

        if($request->has('filter')) {
        	foreach ($request->filter as $key => $filter) {
        		foreach ($filter as $k => $value) {
        			$value = explode(',',$value);
        			if($k == 'is') {
        				$template->where($key,$value);
        			} elseif ($k == '!is') {
        				$template->where($key,'!=',$value);
        			} elseif ($k == 'in') {
        				$template->whereIn($key,$value);
        			} elseif ($k == '!in') {
        				$template->whereNotIn($key,$value);
        			} elseif ($k == 'like') {
        				$template->where($key,'like',$value.'%');
        			} elseif ($k == '!likw') {
        				$template->where($key,'not like',$value.'%');
        			}
        		}
        	}
        }

        $template = $template->paginate($request->input('offset', 10))->appends($request->all());

        if($request->has('field')) {
	        $fields = $request->field;
			$arrayField = explode(',', $fields);

			foreach($template as $dt){
				foreach($arrayField as $field){
					$dt->makeVisible($field);
				}
			}
        }

    	return json::response($template,$request);
    }

    public function indexTemplatebyId(Request $request, $id)
    {
    	$temp = Template::findOrFail($id);

    	return json::response($temp,$request);
    }

    public function updateTemplate(Request $request, $id)
    {
    	$temp = Template::findOrFail($id);
    	$temp->name = $request->data['name'];
    	$temp->checklist = $request->data['checklist'];
    	$temp->items = $request->data['items'];
    	$temp->save();

    	$data['loggable_type'] = 'template';
    	$data['loggable_id'] = $id;
    	$data['action'] = 'update';
    	$data['kwuid'] = $request->user()->id;
    	$data['value'] = $request->all();

    	$this->history($data);

    	return json::response($temp,$request);
    }

    public function deleteTemplate(Request $request, $id)
    {
    	$temp = Template::findOrFail($id);
    	$temp->delete();

    	$data['loggable_type'] = 'template';
    	$data['loggable_id'] = $temp->id;
    	$data['action'] = 'delete';
    	$data['kwuid'] = $request->user()->id;
    	$data['value'] = $request->all();

    	$this->history($data);

    	return json::response($temp,$request);
    }

    public function history($data)
    {
    	$hst = new History;
    	$hst->loggable_type = $data['loggable_type'];
    	$hst->loggable_id = $data['loggable_id'];
    	$hst->action = $data['action'];
    	$hst->kwuid = $data['kwuid'];
    	$hst->value = is_array($data['value'])?json_encode($data['value'], JSON_UNESCAPED_SLASHES):$data['value'];
    	$hst->save();
    }

    public function indexHistory(Request $request)
    {
    	$hst = History::query();

    	$hst = $hst->paginate($request->input('offset', 10))->appends($request->all());

    	return json::response($hst,$request);
    }

    public function historiesById(Request $request, $id)
    {
    	$hst = History::findOrFail($id);

    	return json::response($hst,$request);
    }


}
