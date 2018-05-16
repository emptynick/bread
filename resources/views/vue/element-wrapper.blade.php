@section('element-wrapper')
<div>
    <div :class="'panel panel-bordered '+element.class"
    style="height:100%; margin-bottom:0 !important;"
    v-tooltip.notrigger="{ html: element.i+'_options', visible: isOptionsOpen, class: 'options-tooltip' }">
        <div class="panel-heading" v-if="element.element == 'formfield' && element.class != ''">
            <h3 class="panel-title">@{{ title }}</h3>
            <div class="panel-actions">
                <a class="panel-action voyager-trash" @click="deleteElement()"></a>
                <a class="panel-action voyager-settings open-settings" @click="openOptions()"></a>
            </div>
        </div>
        <div class="panel-body no-drag">
            <div v-if="element.element != 'formfield' || element.class == ''" class="panel-actions floating-actions">
                <a class="panel-action voyager-trash" @click="deleteElement()"></a>
                <a class="panel-action voyager-settings open-settings" @click="openOptions()"></a>
            </div>
            <component :is="element.type" v-bind="element">
                <div slot="options">
					<div class="pull-left">
						<h4>Options</h4>
					</div>
					<div class="pull-right" @click="closeOptions()">
						<span class="voyager-x" style="cursor:pointer;"></span>
					</div>
					<div class="clearfix"></div>
					<div class="form-group" v-if="element.element == 'formfield'">
			            <label>Field</label>
                        <select class="form-control" v-model="element.field">
                            <option v-for="field in builder.fields">
                                @{{ field }}
                            </option>
                        </select>
			        </div>
					<div class="form-group" v-if="element.element == 'formfield'">
			            <label>Style</label>
			            <select class="form-control" v-model="element.class">
							<option value="panel-primary">Blue</option>
							<option value="panel-danger">Red</option>
							<option value="panel-warning">Yellow</option>
							<option value="panel-success">Green</option>
							<option value="">None</option>
						</select>
			        </div>
					<div class="form-group" v-if="element.element == 'formfield' && element.class != ''">
			            <label>Title</label>
			            <input type="text" class="form-control" v-model="element.options.title" v-on:keyup="requestTranslation()">
			        </div>
				</div>

				<div slot="options_after">
                    <div class="checkbox" v-if="element.element == 'formfield'">
						<label><input type="checkbox" v-model="element.translatable">Translatable</label>
					</div>
					<div class="form-group" v-if="element.element == 'formfield' || element.element == 'relationship'">
			            <label>Validation</label>
			            <input type="text" class="form-control" v-model="element.title">
			        </div>
				</div>
            </component>
        </div>
    </div>
</div>
@endsection
<script>
Vue.component('element-wrapper', {
    template: `@yield('element-wrapper')`,
    props: [ 'element' ],
    data: function() {
        return {
            translation: ''
        }
    },
    computed: {
        builder() {
            return builder;
        },
        isOptionsOpen() {
            return builder.isOptionsOpen(this.element.i);
        },
        title: function() {
            if (this.translation != '')
                return this.translation;
            return this.element.options.title;
        },
    },
    methods: {
        openOptions() {
            builder.openOptions(this.element.i);
        },
        closeOptions() {
            builder.openOptions(null);
        },
        deleteElement() {
            builder.deleteElement(this.element.i);
        },
        requestTranslation: _.debounce(function (e) {
            if (this.element.options.title != '')
                this.$bus.$emit('translationRequested', this.element.options.title);
            else
                this.translation = '';
        }, 500)
    },
    mounted: function() {
        var childOptions = this.$children[0].$props.options;

        this.$bus.$on('translationReceived', (key, data) => {
            if (data != "") {
                if (key == this.element.options.title)
                    this.translation = data;

                if (childOptions) {
                    if (childOptions.text ) {

                    }
                }
            }
        });
        this.requestTranslation();

        //Request translations for generic option-fields

        console.log(childOptions);
    },
});
</script>