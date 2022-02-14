import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'

const EditorMenu = (props) =>
  <Toolbar
    className="list-group"
    buttonName="list-group-item"
    actions={[
      {
        name: 'parameters',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('parameters'),
        target: props.path+'/parameters'
      }, {
        name: 'list',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-list',
        label: trans('entries_list_search', {}, 'clacoform'),
        target: props.path+'/list'
      }, {
        name: 'comments',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-comments',
        label: trans('comments', {}, 'clacoform'),
        target: props.path+'/comments'
      }, {
        name: 'categories',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-object-group',
        label: trans('categories', {}, 'clacoform'),
        target: props.path+'/categories'
      }, {
        name: 'keywords',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-font',
        label: trans('keywords', {}, 'clacoform'),
        target: props.path+'/keywords'
      }
    ]}
    onClick={props.autoClose}
  />

EditorMenu.propTypes = {
  path: T.string,
  autoClose: T.func.isRequired
}

export {
  EditorMenu
}
