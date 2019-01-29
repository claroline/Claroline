/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the base application.
 */
registry.add('ClarolineAppBundle', {
  data: {
    types: {
      'boolean'     : () => { return import(/* webpackChunkName: "app-data-boolean" */      '#/main/app/data/types/boolean') },
      'cascade'     : () => { return import(/* webpackChunkName: "app-data-cascade" */      '#/main/app/data/types/cascade') },
      'cascade-enum': () => { return import(/* webpackChunkName: "app-data-cascade-enum" */ '#/main/app/data/types/cascade-enum') },
      'choice'      : () => { return import(/* webpackChunkName: "app-data-choice" */       '#/main/app/data/types/choice') },
      'collection'  : () => { return import(/* webpackChunkName: "app-data-collection" */   '#/main/app/data/types/collection') },
      'color'       : () => { return import(/* webpackChunkName: "app-data-color" */        '#/main/app/data/types/color') },
      'country'     : () => { return import(/* webpackChunkName: "app-data-country" */      '#/main/app/data/types/country') },
      'date'        : () => { return import(/* webpackChunkName: "app-data-date" */         '#/main/app/data/types/date') },
      'date-range'  : () => { return import(/* webpackChunkName: "app-data-date-range" */   '#/main/app/data/types/date-range') },
      'email'       : () => { return import(/* webpackChunkName: "app-data-email" */        '#/main/app/data/types/email') },
      'enum'        : () => { return import(/* webpackChunkName: "app-data-enum" */         '#/main/app/data/types/enum') },
      'enum-plus'   : () => { return import(/* webpackChunkName: "app-data-enum-plus" */    '#/main/app/data/types/enum-plus') },
      'fields'      : () => { return import(/* webpackChunkName: "app-data-fields" */       '#/main/app/data/types/fields') },
      'file'        : () => { return import(/* webpackChunkName: "app-data-file" */         '#/main/app/data/types/file') },
      'filter'      : () => { return import(/* webpackChunkName: "app-data-filter" */       '#/main/app/data/types/filter') },
      'html'        : () => { return import(/* webpackChunkName: "app-data-html" */         '#/main/app/data/types/html') },
      'image'       : () => { return import(/* webpackChunkName: "app-data-image" */        '#/main/app/data/types/image') },
      'ip'          : () => { return import(/* webpackChunkName: "app-data-ip" */           '#/main/app/data/types/ip') },
      'locale'      : () => { return import(/* webpackChunkName: "app-data-locale" */       '#/main/app/data/types/locale') },
      'number'      : () => { return import(/* webpackChunkName: "app-data-number" */       '#/main/app/data/types/number') },
      'password'    : () => { return import(/* webpackChunkName: "app-data-country" */      '#/main/app/data/types/password') },
      'score'       : () => { return import(/* webpackChunkName: "app-data-score" */        '#/main/app/data/types/score') },
      'storage'     : () => { return import(/* webpackChunkName: "app-data-storage" */      '#/main/app/data/types/storage') },
      'string'      : () => { return import(/* webpackChunkName: "app-data-string" */       '#/main/app/data/types/string') },
      'translated'  : () => { return import(/* webpackChunkName: "app-data-translated" */   '#/main/app/data/types/translated') },
      'translation' : () => { return import(/* webpackChunkName: "app-data-translation" */  '#/main/app/data/types/translation') },
      'url'         : () => { return import(/* webpackChunkName: "app-data-url" */          '#/main/app/data/types/url') },
      'username'    : () => { return import(/* webpackChunkName: "app-data-username" */     '#/main/app/data/types/username') }
    }
  }
})
