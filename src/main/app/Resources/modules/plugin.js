/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the base application.
 */
registry.add('ClarolineAppBundle', {
  store: {
    'config'  : () => { return import(/* webpackChunkName: "app-store-config" */ '#/main/app/config/store') },
    'security': () => { return import(/* webpackChunkName: "app-store-security" */ '#/main/app/security/store') }
  },
  data: {
    types: {
      'address'     : () => { return import(/* webpackChunkName: "app-data-type-address" */      '#/main/app/data/types/address') },
      'boolean'     : () => { return import(/* webpackChunkName: "app-data-type-boolean" */      '#/main/app/data/types/boolean') },
      'cascade'     : () => { return import(/* webpackChunkName: "app-data-type-cascade" */      '#/main/app/data/types/cascade') },
      'cascade-enum': () => { return import(/* webpackChunkName: "app-data-type-cascade-enum" */ '#/main/app/data/types/cascade-enum') },
      'choice'      : () => { return import(/* webpackChunkName: "app-data-type-choice" */       '#/main/app/data/types/choice') },
      'collection'  : () => { return import(/* webpackChunkName: "app-data-type-collection" */   '#/main/app/data/types/collection') },
      'country'     : () => { return import(/* webpackChunkName: "app-data-type-country" */      '#/main/app/data/types/country') },
      'currency'    : () => { return import(/* webpackChunkName: "app-data-type-currency" */     '#/main/app/data/types/currency') },
      'date'        : () => { return import(/* webpackChunkName: "app-data-type-date" */         '#/main/app/data/types/date') },
      'date-range'  : () => { return import(/* webpackChunkName: "app-data-type-date-range" */   '#/main/app/data/types/date-range') },
      'email'       : () => { return import(/* webpackChunkName: "app-data-type-email" */        '#/main/app/data/types/email') },
      'enum'        : () => { return import(/* webpackChunkName: "app-data-type-enum" */         '#/main/app/data/types/enum') },
      'enum-plus'   : () => { return import(/* webpackChunkName: "app-data-type-enum-plus" */    '#/main/app/data/types/enum-plus') },
      'fields'      : () => { return import(/* webpackChunkName: "app-data-type-fields" */       '#/main/app/data/types/fields') },
      'file'        : () => { return import(/* webpackChunkName: "app-data-type-file" */         '#/main/app/data/types/file') },
      'filter'      : () => { return import(/* webpackChunkName: "app-data-type-filter" */       '#/main/app/data/types/filter') },
      'html'        : () => { return import(/* webpackChunkName: "app-data-type-html" */         '#/main/app/data/types/html') },
      'image'       : () => { return import(/* webpackChunkName: "app-data-type-image" */        '#/main/app/data/types/image') },
      'ip'          : () => { return import(/* webpackChunkName: "app-data-type-ip" */           '#/main/app/data/types/ip') },
      'locale'      : () => { return import(/* webpackChunkName: "app-data-type-locale" */       '#/main/app/data/types/locale') },
      'number'      : () => { return import(/* webpackChunkName: "app-data-type-number" */       '#/main/app/data/types/number') },
      'password'    : () => { return import(/* webpackChunkName: "app-data-type-password" */     '#/main/app/data/types/password') },
      'progression' : () => { return import(/* webpackChunkName: "app-data-type-progression" */  '#/main/app/data/types/progression') },
      'score'       : () => { return import(/* webpackChunkName: "app-data-type-score" */        '#/main/app/data/types/score') },
      'storage'     : () => { return import(/* webpackChunkName: "app-data-type-storage" */      '#/main/app/data/types/storage') },
      'string'      : () => { return import(/* webpackChunkName: "app-data-type-string" */       '#/main/app/data/types/string') },
      'time'        : () => { return import(/* webpackChunkName: "app-data-type-time" */         '#/main/app/data/types/time') },
      'timezone'    : () => { return import(/* webpackChunkName: "app-data-type-timezone" */     '#/main/app/data/types/timezone') },
      'translated'  : () => { return import(/* webpackChunkName: "app-data-type-translated" */   '#/main/app/data/types/translated') },
      'translation' : () => { return import(/* webpackChunkName: "app-data-type-translation" */  '#/main/app/data/types/translation') },
      'type'        : () => { return import(/* webpackChunkName: "app-data-type-type" */         '#/main/app/data/types/type') },
      'url'         : () => { return import(/* webpackChunkName: "app-data-type-url" */          '#/main/app/data/types/url') },
      'username'    : () => { return import(/* webpackChunkName: "app-data-type-username" */     '#/main/app/data/types/username') }
    }
  }
})
