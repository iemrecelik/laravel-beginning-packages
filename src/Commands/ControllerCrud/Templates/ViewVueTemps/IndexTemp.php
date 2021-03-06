<?php
return function($params){

extract($params);

$lwModelName = strtolower($modelName);

$fields = array_merge($addLangFields, $addFields);

$fieldsHtml = '';
$fieldsTbl = '';
foreach ($fields as $field) {

  $fieldsTbl .= '
        <th>{{ $t("messages.'.$field['name'].'") }}</th>
  ';
  $fieldsTbl = trim($fieldsTbl);

  switch ($field['type']) {
    case 'date':
      $fieldsHtml .= '
        { 
          "data": "'.$field['name'].'",
          "render": (data, type, row) => {
            return this.unixTimestamp(data);
          }
        },';
      break;
    
    default:
      $fieldsHtml .= "\n\t\t\t\t{ \"data\": \"{$field['name']}\" },";
      $fieldsHtml = ltrim($fieldsHtml);
      break;
  }
}

if ($crudType !== 'advanced') {
$editBtnRow = 'row += this.editBtnHtml(id);';
$editBtnHtml = '
    editBtnHtml: function(id){
      return  `
        <span 
          data-toggle="tooltip" data-placement="top" 
          title="${this.$t(\'messages.edit\')}"
        >
          <button type="button" class="btn btn-sm btn-success"
            data-toggle="modal" data-target="${this.modalSelector}"
            data-component="${this.formTitleName}-edit-component" 
            data-datas=\'{
              "id": ${id},
              "formTitleName": "${this.formTitleName}"
            }\'
          >
            <i class="icon ion-md-create"></i>
          </button>
        </span>`;
    },
';

$addBtn = '
      <tr>
        <th colspan="'.(count($fields) + 1).'">
          <button type="button" class="btn btn-primary"
            data-toggle="modal" 
            :data-target="modalSelector"
            :data-datas=\'`{"formTitleName": "\${formTitleName}"}`\'
            :data-component="`${formTitleName}-create-component`"
          >
            {{ $t(\'messages.add\') }}
          </button>
        </th>
      </tr>
';
$addBtn = trim($addBtn);

$redirectCreatePage = '';
$createUrl = '';

$importCrudComp = '
import createComponent from \'./CreateComponent\';
import editComponent from \'./EditComponent\';
';
$importCrudComp = trim($importCrudComp);

$crudComp = '
    [formTitleName + \'-create-component\']: createComponent,
    [formTitleName + \'-edit-component\']: editComponent,
';
$crudComp = trim($crudComp);
}else{
  $editBtnRow = 'row += this.editBtnHtml(id);';
  $editBtnHtml = '
    editBtnHtml: function(id){
      return  `
        <span 
          data-toggle="tooltip" data-placement="top" 
          title="${this.$t(\'messages.edit\')}"
        >
          <a class="btn btn-sm btn-success"
            href="${this.routes.index}/${id}/edit"
          >
            <i class="icon ion-md-create"></i>
          </a>
        </span>`;
    },
  ';

  $addBtn = '
      <tr>
        <th colspan="'.(count($fields) + 1).'">
          <button type="button" class="btn btn-primary"
          @click="redirectCreatePage"
          >
            {{ $t(\'messages.add\') }}
          </button>
        </th>
      </tr>
  ';
  $addBtn = trim($addBtn);
  $redirectCreatePage = '
    redirectCreatePage: function () {
      window.location.href = this.createUrl;
    },
  ';
  $createUrl = '
    createUrl: function(){
      return this.routes.index + \'/create\';
    },
  ';

  $importCrudComp = '';
  $crudComp = '';
}

$crudComp .= '
    [formTitleName + \'-show-component\']: showComponent,
    [formTitleName + \'-delete-component\']: deleteComponent,
';
$crudComp = trim($crudComp);

if ($imgModelName) {

$imgFilters = '
    \'imgFilters\',
';

$setImgFilters = '
    \'setImgFilters\',
';
$setImgFiltersCreated = '
    this.setImgFilters(this.ppimgfilters);
';

$ppimgfilt = '
    ppimgfilters: {
      type: Object,
      required: true,
    },
';

$imageBtnRow = 'row += this.imageBtnHtml(id);';

$imagesComp = '
[formTitleName + \'-images-component\']: imagesComponent,
';

$importImagesComp = '
import imagesComponent from \'./ImagesComponent\';
';

$imageBtnHtml = '
    imageBtnHtml: function(id){
      return  `
        <span 
            data-toggle="tooltip" data-placement="top" 
            title="${this.$t(\'messages.image\')}"
          >
          <button type="button" class="btn btn-sm btn-primary"
            data-toggle="modal" data-target="${this.modalSelector}"
            data-component="${this.formTitleName}-images-component" 
            data-datas=\'{
              "id": ${id},
              "formTitleName": "${this.formTitleName}"
            }\'
          >
            <i class="icon ion-md-camera"></i>
          </button>
        </span>`;
    },
';
}else{
  $imgFilters = '';
  $setImgFilters = '';
  $setImgFiltersCreated = '';
  $ppimgfilt = '';
  $importImagesComp = '';
  $imagesComp = '';
  $imageBtnRow = '';
  $imageBtnHtml = '';
}

return '
<template>
<div>
  <table class="res-dt-table table table-striped table-bordered" 
  style="width:100%">
    <thead>
      <tr>
        '.$fieldsTbl.'
        <th>{{ $t("messages.processes") }}</th>
      </tr>
    </thead>
    <tfoot>
      '.$addBtn.'
    </tfoot>
  </table>

  <!-- Modal -->
  <div class="modal fade" tabindex="-1" role="dialog" 
    aria-labelledby="formModalLongTitle" aria-hidden="true"
    data-backdrop="static" :id="modalIDName"
  >
    <div class="modal-dialog" role="document">
      <component
        v-if="formModalBody.show"
        :is="formModalBody.component"
        :ppdatas="formModalBody.datas"
      >
      </component>
    </div>
  </div>
  
</div>
</template>

<script>
'.$importCrudComp.'
import showComponent from \'./ShowComponent\';
import deleteComponent from \'./DeleteComponent\';
'.trim($importImagesComp).'

import { mapState, mapMutations } from \'vuex\';

let formTitleName = \''.$lwModelName.'\';

export default {
  name: this.componentTitleName,
  data () {
    return {
      modalIDName: \'formModalLong\',
      formTitleName: \''.$lwModelName.'\',
      dataTable: null,
    };
  },
  props: {
    pproutes: {
      type: Object,
      required: true,
    },
    pperrors: {
      type: Object,
      required: true,
    },'.$ppimgfilt.'
  },
  computed: {
    ...mapState([
      \'formModalBody\',
      \'routes\',
      \'errors\',
      \'token\','.$imgFilters.'
    ]),
    cformTitleName: function(){
      return _.capitalize(this.formTitleName);
    },
    componentTitleName: function(){
      return _.capitalize(this.formTitleName) + \'Component\';
    },
    modalSelector: function(){
      return \'#\' + this.modalIDName;
    },'.$createUrl.'
  },
  methods: {
    ...mapMutations([
      \'setRoutes\',
      \'setErrors\',
      \'setEditItem\','.$setImgFilters.'
    ]),
    processesRow: function(id){
      let row = \'\';
      '.$editBtnRow.'
      row += this.deleteBtnHtml(id);
      '.$imageBtnRow.'
      return row;
    },
    '.$editBtnHtml.'
    deleteBtnHtml: function(id){
      return  `
        <span 
            data-toggle="tooltip" data-placement="top" 
            title="${this.$t(\'messages.delete\')}"
          >
          <button type="button" class="btn btn-sm btn-danger"
            data-toggle="modal" data-target="${this.modalSelector}"
            data-component="${this.formTitleName}-delete-component" 
            data-datas=\'{
              "id": ${id},
              "formTitleName": "${this.formTitleName}"
            }\'
          >
            <i class="icon ion-md-trash"></i>
          </button>
        </span>`;
    },
    '.$imageBtnHtml.$redirectCreatePage.'
  },
  created(){
    this.setRoutes(this.pproutes);
    this.setErrors(this.pperrors);'.$setImgFiltersCreated.'
  },
  mounted(){
    this.showModalBody(this.modalSelector);

    this.dataTable = this.dataTableRun({
      jQDomName: \'.res-dt-table\',
      url: this.routes.dataList,
      columns: [
        '.$fieldsHtml.'
        {
          "orderable": false,
          "searchable": false,
          "sortable": false,
          "data": "'.$fieldIDName.'",
          "render": ( data, type, row ) => {
              return this.processesRow(data);
          },
          "defaultContent": ""
        },
      ],
    });
  },
  components: {
    '.$crudComp.'
    '.trim($imagesComp).'
  }
}
</script>
';
};
