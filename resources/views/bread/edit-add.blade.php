@extends('voyager::master')
@section('page_title', __('voyager::generic.'.($content->getKey() ? 'edit' : 'add')).' '.$bread->display_name_singular)

@section('content')
<div id="bread-edit">
    <vue-snotify></vue-snotify>
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $bread->icon }}"></i>
            {{ __('voyager::generic.'.($content->getKey() ? 'edit' : 'add')).' '.$bread->display_name_singular }}
        </h1>
        @if ($model->isTranslatable)
        <language-switcher :languages="{{ json_encode(config('voyager.multilingual.locales')) }}"></language-switcher>
        @endif
    </div>
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">
                @include('voyager::alerts')
                <form action="@if($content->getKey()){{ route('voyager.'.$bread->slug.'.update', $content->getKey()) }}@else{{ route('voyager.'.$bread->slug.'.store') }}@endif"
                        method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    @if($content->getKey())
                        {{ method_field("PUT") }}
                    @endif
                        <div v-for="(item, key) in elements" :class="'col-md-'+item.width">
                            <div class="panel">
                                <div class="panel-body">
                                    <div :class="'form-group '+(hasError(item.field) ? 'has-error' : '')">
                                        <component
                                            :is="'formfield-'+item.type"
                                            :options="item.options"
                                            :name="item.field"
                                            :show="'{{ $content->getKey() ? 'edit' : 'add' }}'"
                                            :input="getContentForField(item.field)"
                                            ></component>

                                        <span class="help-block" style="color:#f96868" v-if="hasError(item.field)">
                                            <ul>
                                                <li v-for="msg in getErrors(item.field)">
                                                    @{{ msg }}
                                                </li>
                                            </ul>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <button type="submit" name="submit_action" value="" class="btn btn-primary">{{ __('voyager::generic.save') }}</button>
                        @can('edit', $model)
                            <button type="submit" name="submit_action" value="edit" class="btn btn-primary">Save and Edit</button>
                        @endcan
                        @can('add', $model)
                            <button type="submit" name="submit_action" value="add" class="btn btn-primary">Save and create new</button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>

</script>
<script src="{{ route('voyager.bread.scripts') }}"></script>
@foreach(\Bread\BreadFacade::formfields() as $formfield)
@include($formfield->getComponent('view'))
@endforeach
@include('bread::components.language-switcher')
<script>
new Vue({
    el: "#bread-edit",
    data: {
        elements: {!! $layout->elements->toJson() !!},
        content: {!! collect($content)->merge(old())->toJson() !!},
        errors: {!! $errors->toJson() !!},
    },
    methods: {
        getContentForField: function(field) {
            if (this.content) {
                return this.content[field];
            } else {
                return '';
            }
        },
        hasError: function(field) {
            if (this.getErrors(field)) {
                return true;
            }
            return false;
        },
        getErrors: function(field) {
            return this.errors[field];
        },
    },
});
</script>
@endsection
@section('css')
<link rel="stylesheet" href="{{ route('voyager.bread.styles') }}">
@endsection