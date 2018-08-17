/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the base application.
 */
registry.add('app', {
  data: {
    types: {
      'boolean'     : () => { return import(/* webpackChunkName: "app-data-boolean" */      '#/main/app/data/boolean') },
      'cascade'     : () => { return import(/* webpackChunkName: "app-data-cascade" */      '#/main/app/data/cascade') },
      'cascade-enum': () => { return import(/* webpackChunkName: "app-data-cascade-enum" */ '#/main/app/data/cascade-enum') },
      'choice'      : () => { return import(/* webpackChunkName: "app-data-choice" */       '#/main/app/data/choice') },
      'color'       : () => { return import(/* webpackChunkName: "app-data-color" */        '#/main/app/data/color') },
      'country'     : () => { return import(/* webpackChunkName: "app-data-country" */      '#/main/app/data/country') },
      'date'        : () => { return import(/* webpackChunkName: "app-data-date" */         '#/main/app/data/date') },
      'date-range'  : () => { return import(/* webpackChunkName: "app-data-date-range" */   '#/main/app/data/date-range') },
      'email'       : () => { return import(/* webpackChunkName: "app-data-email" */        '#/main/app/data/email') },
      'enum'        : () => { return import(/* webpackChunkName: "app-data-enum" */         '#/main/app/data/enum') },
      'enum-plus'   : () => { return import(/* webpackChunkName: "app-data-enum-plus" */    '#/main/app/data/enum-plus') },
      'fields'      : () => { return import(/* webpackChunkName: "app-data-fields" */       '#/main/app/data/fields') },
      'file'        : () => { return import(/* webpackChunkName: "app-data-file" */         '#/main/app/data/file') },
      'html'        : () => { return import(/* webpackChunkName: "app-data-html" */         '#/main/app/data/html') },
      'image'       : () => { return import(/* webpackChunkName: "app-data-image" */        '#/main/app/data/image') },
      'ip'          : () => { return import(/* webpackChunkName: "app-data-ip" */           '#/main/app/data/ip') },
      'locale'      : () => { return import(/* webpackChunkName: "app-data-locale" */       '#/main/app/data/locale') },
      'number'      : () => { return import(/* webpackChunkName: "app-data-number" */       '#/main/app/data/number') },
      'password'    : () => { return import(/* webpackChunkName: "app-data-country" */      '#/main/app/data/password') },
      'score'       : () => { return import(/* webpackChunkName: "app-data-score" */        '#/main/app/data/score') },
      'storage'     : () => { return import(/* webpackChunkName: "app-data-storage" */      '#/main/app/data/storage') },
      'string'      : () => { return import(/* webpackChunkName: "app-data-string" */       '#/main/app/data/string') },
      'translated'  : () => { return import(/* webpackChunkName: "app-data-translated" */   '#/main/app/data/translated') },
      'translation' : () => { return import(/* webpackChunkName: "app-data-translation" */  '#/main/app/data/translation') },
      'url'         : () => { return import(/* webpackChunkName: "app-data-url" */          '#/main/app/data/url') },
      'username'    : () => { return import(/* webpackChunkName: "app-data-username" */     '#/main/app/data/username') }
    }
  }
})
