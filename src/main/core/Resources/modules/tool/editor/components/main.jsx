import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Routes, RouteTypes} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/components/page'

import {EditorRights} from '#/main/core/tool/editor/containers/rights'
import {EditorHistory} from '#/main/core/tool/editor/components/history'
import {Form} from '#/main/app/content/form/containers/form'
import {selectors} from '#/main/core/tool/editor/store'

const ToolEditor = (props) => {
  useEffect(() => {
    if (props.loaded) {
      props.load(props.tool)
    }
  }, [props.contextType, props.contextId, props.name, props.loaded])

  return (
    <ToolPage
      title={trans('parameters')}
      actions={[
        {
          name: 'edit-poster',
          type: CALLBACK_BUTTON,
          label: trans('Modifier la couverture'),
          callback: () => true
        }
      ]}
      menu={{
        nav: [].concat(props.menu, [
          {
            name: 'overview',
            label: trans('about'),
            type: LINK_BUTTON,
            target: props.path+'/edit',
            displayed: !isEmpty(props.children),
            exact: true
          }, {
            name: 'permissions',
            label: trans('permissions'),
            type: LINK_BUTTON,
            target: props.path+'/edit/permissions',
            displayed: hasPermission('administrate', props.tool)
          }, {
            name: 'history',
            label: trans('history'),
            type: LINK_BUTTON,
            target: props.path+'/edit/history'
          }
        ]),
        actions: [
          {
            name: 'close',
            label: trans('close'),
            icon: 'fa far fa-fw fa-times-circle',
            type: LINK_BUTTON,
            target: props.path,
            exact: true
          }
        ]
      }}
    >
      <Form
        disabled={!props.loaded}
        className="my-3"
        name={selectors.STORE_NAME}
        dataPart="data"
        target={['apiv2_tool_configure', {
          name: props.name,
          context: props.contextType,
          contextId: props.contextId
        }]}
        onSave={(savedData) => props.refresh(props.name, savedData, props.contextType)}
        buttons={true}
      >
        <Routes
          path={props.path+'/edit'}
          routes={[
            {
              path: '/permissions',
              component: EditorRights,
              disabled: !hasPermission('administrate', props.tool)
            }, {
              path: '/history',
              component: EditorHistory
            }
          ]
            .concat(props.pages || [])
            .concat([
              {
                path: '/',
                disabled: isEmpty(props.children),
                render: () => props.children,
                exact: true
              }
            ])
          }
          redirect={props.redirect}
        />
      </Form>
    </ToolPage>
  )
}

ToolEditor.propTypes = {
  /**
   * A func to return the custom parameters data
   */
  additionalData: T.func,

  menu: T.arrayOf(T.shape({

  })),
  pages: T.arrayOf(T.shape(
    RouteTypes.propTypes
  )),

  // from store
  loaded: T.bool.isRequired,
  path: T.string.isRequired,
  name: T.string,
  tool: T.object,
  contextType: T.string.isRequired,
  contextId: T.string,
  load: T.func.isRequired,
  refresh: T.func.isRequired
}

ToolEditor.defaultProps = {
  menu: []
}

export {
  ToolEditor
}
