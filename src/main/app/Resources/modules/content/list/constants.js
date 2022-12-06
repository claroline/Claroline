import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'

import {constants as paginationConst} from '#/main/app/content/pagination/constants'

import {DataTable} from '#/main/app/content/list/components/view/data-table'
import {DataGrid} from '#/main/app/content/list/components/view/data-grid'

import {constants as treeConst} from '#/main/app/content/tree/constants'

const DISPLAY_TABLE    = 'table'
const DISPLAY_TABLE_SM = 'table-sm'
const DISPLAY_TILES    = 'tiles'
const DISPLAY_TILES_SM = 'tiles-sm'
const DISPLAY_LIST_SM  = 'list-sm'
const DISPLAY_LIST     = 'list'

const DEFAULT_DISPLAY_MODE = DISPLAY_TABLE
const DEFAULT_DISPLAY_MODES = [
  DISPLAY_TABLE_SM,
  DISPLAY_TABLE,
  DISPLAY_LIST_SM,
  DISPLAY_LIST,
  DISPLAY_TILES_SM,
  DISPLAY_TILES
]

/**
 * List of implemented display modes for lists.
 *
 * @type {object}
 */
const DISPLAY_MODES = merge({
  [DISPLAY_TABLE_SM]: {
    icon: 'fa fa-fw fa-list',
    label: trans('list_display_table_sm'),
    component: DataTable,
    options: {
      size: 'sm',
      filterColumns: true // used to know if we need to enable the tool to filter displayed data properties
    }
  },
  [DISPLAY_TABLE]: {
    icon: 'fa fa-fw fa-table-list',
    label: trans('list_display_table'),
    component: DataTable,
    options: {
      size: 'lg',
      filterColumns: true // used to know if we need to enable the tool to filter displayed data properties
    }
  },
  [DISPLAY_LIST_SM]: {
    icon: 'fa fa-fw fa-list-ul',
    label: trans('list_display_list_sm'),
    component: DataGrid,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'sm',
      orientation: 'row'
    }
  },
  [DISPLAY_LIST]: {
    icon: 'fa fa-fw fa-align-justify',
    label: trans('list_display_list'),
    component: DataGrid,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'lg',
      orientation: 'row'
    }
  },
  [DISPLAY_TILES_SM]: {
    icon: 'fa fa-fw fa-th',
    label: trans('list_display_tiles_sm'),
    component: DataGrid,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'sm',
      orientation: 'col'
    }
  },
  [DISPLAY_TILES]: {
    icon: 'fa fa-fw fa-th-large',
    label: trans('list_display_tiles'),
    component: DataGrid,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'lg',
      orientation: 'col'
    }
  }
}, treeConst.DISPLAY_MODES)

// reexport pagination constants here for retro compatibility
export const constants = Object.assign({}, paginationConst, {
  DISPLAY_MODES,
  DEFAULT_DISPLAY_MODE,
  DEFAULT_DISPLAY_MODES,
  DISPLAY_TABLE,
  DISPLAY_TABLE_SM,
  DISPLAY_TILES,
  DISPLAY_TILES_SM,
  DISPLAY_LIST,
  DISPLAY_LIST_SM
})
