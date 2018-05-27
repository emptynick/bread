@extends('voyager::master')

@section('page_title', __('bread::manager.edit_view_for', ['bread' => $bread->display_name_plural]))

@section('page_header')
<h1 class="page-title">
    <i class="voyager-lightbulb"></i>
    {{ __('bread::manager.edit_views_for', ['bread' => $bread->display_name_plural]) }}
</h1>
@stop
@section('breadcrumbs')
<ol class="breadcrumb hidden-xs">
    @if(count(Request::segments()) == 1)
    <li class="active"><i class="voyager-boat"></i> {{ __('voyager::generic.dashboard') }}</li>
    @else
    <li class="active">
        <a href="{{ route('voyager.dashboard')}}"><i class="voyager-boat"></i> {{ __('voyager::generic.dashboard') }}</a>
    </li>
    @endif
    <li class="active"><a href="{{ route('voyager.bread.index') }}">{{ __('bread::manager.bread_manager') }}</a></li>
    <li class="active"><a href="{{ route('voyager.bread.edit', $bread->name) }}">{{ $bread->display_name_plural }}</a></li>
    <li class="active"><a href="{{ route('voyager.bread.views.edit', $bread->name) }}">{{ __('bread::manager.views') }}</a></li>
</ol>
@endsection
@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div id="list-builder">
                    <vue-snotify></vue-snotify>
                    <div class="col-md-12">
                        <div class="dropdown" style="display:inline">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                Add Element
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li class="dropdown-header">Formfields</li>
                                @foreach(\Bread\BreadFacade::formfields()->where('element_type', 'formfield') as $formfield)
            					<li>
                                    <a href="#" v-on:click="addElement('formfield', '{{ $formfield->getCodeName() }}')">
                                        {{ $formfield->getName() }}
                                    </a>
                                </li>
            					@endforeach
                                <li class="dropdown-header">Relationships</li>
                                @foreach($relationships as $relationship)
            					<li>
                                    <a href="#" v-on:click="addElement('relationship', '{{ $relationship }}')">
                                        {{ $relationship }}
                                    </a>
                                </li>
            					@endforeach
                            </ul>
                        </div>
                        <div class="dropdown" style="display:inline">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                List (@{{ this.currentList.name }})
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li v-for="(list, key) in this.lists" v-if="list.type == 'list'">
                                    <a href="#" v-on:click="changeList(list.name)">
                                        <strong v-if="list.name == currentList.name">
                                            @{{ list.name }}
                                        </strong>
                                        <span v-else>
                                            @{{ list.name }}
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <button @click="createNewListPrompt()" class="btn btn-primary">New List</button>
                        <button @click="saveLists()" class="btn btn-primary">Save Lists</button>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row fake-table-hd">
                        <div class="col-xs-2">Field</div>
                        <div class="col-xs-2">Label</div>
                        <div class="col-xs-1">Type</div>
                        <div class="col-xs-1">Searchable</div>
                        <div class="col-xs-1">Orderable</div>
                        <div class="col-xs-1">Initial Order</div>
                        <div class="col-xs-1">Invisible</div>
                        <div class="col-xs-1">Linked to</div>
                        <div class="col-xs-2">Actions</div>
                    </div>
                    <draggable v-model="this.currentList.elements">
                        <div class="row row-dd"
                             v-for="element in currentList.elements"
                             :key="element.id"
                             v-tooltip.notrigger="{ html: element.id+'_options', visible: isOptionsOpen(element.id), class: 'options-tooltip' }">
                            <div class="col-xs-2">
                                <select class="form-control" v-model="element.field">
                                    <optgroup label="test">
                                        <option v-for="field in fields">
                                            @{{ field }}
                                        </option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-xs-2">
                                <input type="text" class="form-control" v-model="element.label">
                            </div>
                            <div class="col-xs-1">@{{ element.type }}</div>
                            <div class="col-xs-1"><input type="checkbox" v-model="element.searchable"></div>
                            <div class="col-xs-1"><input type="checkbox" v-model="element.orderable"></div>
                            <div class="col-xs-1">
                                <input type="radio" :value="element.id" v-model="currentList.initial_ordered">
                            </div>
                            <div class="col-xs-1">
                                <input type="checkbox" v-model="element.invisible">
                            </div>
                            <div class="col-xs-1">@{{ element.id }}</div>
                            <div class="col-xs-2">
                                <button class="btn btn-primary" v-on:click="openOptions(element.id)">Options</button>
                                <button class="btn btn-danger">Delete</button>
                            </div>
                            <div :id="element.id+'_options'">
                                <component :is="element.type" v-bind="element" :i="element.id">
                                    <div slot="options">
                    					<div class="pull-left">
                    						<h4>Options</h4>
                    					</div>
                    					<div class="pull-right" @click="openOptions(null)">
                    						<span class="voyager-x" style="cursor:pointer;"></span>
                    					</div>
                    					<div class="clearfix"></div>
                    				</div>

                    				<div slot="options_after">

                    				</div>
                                </component>
                            </div>
                        </div>
                    </draggable>

                    <textarea>@{{ this.currentList }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('javascript')
<script src="{{ asset('vendor/bread/js/translatable.js') }}"></script>
<script src="{{ asset('vendor/bread/js/app.js') }}"></script>

@foreach(\Bread\BreadFacade::formfields() as $formfield)
@include($formfield->getComponent())
<input type="hidden" id="{{ $formfield->getCodeName() }}_default_options" value="{{ $formfield->getOptions(false) }}">
@endforeach

<script>
Vue.prototype.$type = 'list';

var builder = new Vue({
    el: "#list-builder",
    data: {
        editing: {{ $editing }},
        lists: {!! json_encode($lists->values(), JSON_PRETTY_PRINT) !!},
        currentListId: 0,
        fields: {!! json_encode($fields) !!},
        currentOptionId: null,
    },
    computed: {
        currentList: function() {
            return this.lists[this.currentListId];
        },
    },
    methods: {
        isOptionsOpen: function(id)
        {
            if (this.currentOptionId == id)
                return true;

            return false;
        },
        openOptions: function(id) {
            this.currentOptionId = id;
        },
        createNewListPrompt: function() {
            this.$snotify.html(`<div class="snotifyToast__title">New List</div>
  <div class="snotifyToast__body">Name:<input id="list-name" type="text" class="form-control"></div> `, {
                timeout: 5000,
                showProgressBar: true,
                closeOnClick: false,
                pauseOnHover: true,
                type: 'confirm',
                buttons: [
                    {text: 'Save', action: (toast) => {
                        var value = document.getElementById('list-name').value;
                        if (value != "") {
                            this.createNewList(value);
                            this.$snotify.remove(toast.id);
                        }
                    }},
                    {text: 'Cancel', action: (toast) => this.$snotify.remove(toast.id)},
                ]
            });
        },
        createNewList: function(name) {
            let layout = {
                name: name,
                type: "list",
                elements: [],
            };
            let layoutExists = false;
            this.lists.forEach(function(list) {
                if (list.name == name)
                    layoutExists = true;
            });

            if (layoutExists) {
                this.$snotify.error('This name already exists. Please choose another.', 'Error', {
                    timeout: 5000,
                    showProgressBar: false,
                    closeOnClick: false,
                    pauseOnHover: true,
                    buttons: [
                        {text: 'Ok', action: (toast) => {
                            this.createNewListPrompt();
                            this.$snotify.remove(toast.id);
                        }
                        },
                        {text: 'Cancel', action: (toast) => this.$snotify.remove(toast.id)},
                    ]
                });
                return;
            }
            this.lists.push(layout);
            this.currentListId = this.lists.length -1;
        },
        addElement: function(type, codename) {
            let options = JSON.parse($('#'+codename+'_default_options').val());
            let newitem = {
                options: options,
                type: codename,
                element: type,
                field: null,
                id: this.currentList.elements.length,
            };
            this.currentList.elements.push(newitem);
        },
        saveLists: function() {
            
        },
    },
    mounted: function() {

    }
});
</script>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bread/css/app.css') }}">
<style>
.options-tooltip {
    z-index: 10000 !important;
    width: 150rem;
    background-color: #353d47 !important;
}
.drag-handle {
    font-size:16px;
    vertical-align:middle;
    -webkit-transform: rotate(-45deg);
    -moz-transform: rotate(-45deg);
    -o-transform: rotate(-45deg);
    -ms-transform: rotate(-45deg);
    transform: rotate(-45deg);
}
.dropdown .dropdown-header {
    font-weight: bold;
    padding-left: 10px;
}

.dropdown strong {
    font-weight: bold;
}
</style>
@endsection
