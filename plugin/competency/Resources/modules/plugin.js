import {registry} from '#/main/app/plugins/registry'

registry.add('competency', {
  data: {
    types: {
      'competency_scale' : () => { return import(/* webpackChunkName: "plugin-competency-data-scale" */   '#/plugin/competency/data/types/scale') },
      'ability'          : () => { return import(/* webpackChunkName: "plugin-competency-data-ability" */ '#/plugin/competency/data/types/ability') }
    },
  }
})