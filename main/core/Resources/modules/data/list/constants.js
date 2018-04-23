import {trans} from '#/main/core/translation'

import {DataTable} from '#/main/core/data/list/components/view/data-table.jsx'
import {DataGrid} from '#/main/core/data/list/components/view/data-grid.jsx'

/**
 * Default configuration for list.
 * By default, all implemented features are enabled.
 *
 * @type {object}
 */
const DEFAULT_FEATURES = {
  filterable: true,
  sortable  : true,
  selectable: true,
  paginated : true
}

const DISPLAY_TABLE    = 'table'
const DISPLAY_TABLE_SM = 'table-sm'
const DISPLAY_TILES    = 'tiles'
const DISPLAY_TILES_SM = 'tiles-sm'
const DISPLAY_LIST_SM  = 'list-sm'
const DISPLAY_LIST     = 'list'

const DEFAULT_DISPLAY_MODE = DISPLAY_TABLE

/**
 * List of implemented display modes for lists.
 *
 * @type {object}
 */
const DISPLAY_MODES = {
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
    icon: 'fa fa-fw fa-th-list',
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
}

const AVAILABLE_PAGE_SIZES = [10, 20, 50, 100, -1] // -1 is for all
const DEFAULT_PAGE_SIZE    = AVAILABLE_PAGE_SIZES[1]

// todo : implement me
const DEFAULT_TRANSLATIONS = {
  domain: 'platform',
  keys: {
    searchPlaceholder:     'list_search_placeholder',
    emptyPlaceholder:      'list_no_results',
    countResults:          'list_results_count',
    deleteConfirmTitle:    'objects_delete_title',
    deleteConfirmQuestion: 'objects_delete_question'
  }
}

export const constants = {
  AVAILABLE_PAGE_SIZES,
  DEFAULT_PAGE_SIZE,
  DEFAULT_FEATURES,
  DISPLAY_MODES,
  DEFAULT_DISPLAY_MODE,
  DISPLAY_TABLE,
  DISPLAY_TABLE_SM,
  DISPLAY_TILES,
  DISPLAY_TILES_SM,
  DISPLAY_LIST,
  DEFAULT_TRANSLATIONS
}
