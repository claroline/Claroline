import React, {createElement, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentTabs} from '#/main/app/content/components/tabs'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ContentLoader} from '#/main/app/content/components/loader'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {constants} from '#/plugin/history/constants'

const HistoryModal = (props) => {
  const [section, changeSection] = useState('workspaces')

  return (
    <Modal
      {...omit(props, 'loaded', 'results', 'getHistory')}
      icon="fa fa-fw fa-history"
      title={trans('history', {}, 'history')}
      onEntering={props.getHistory}
      size="sm"
    >
      <ContentTabs
        sections={[
          {
            name: 'workspaces',
            type: CALLBACK_BUTTON,
            label: trans('workspaces'),
            active: 'workspaces' === section,
            callback: () => changeSection('workspaces')
          }, {
            name: 'resources',
            type: CALLBACK_BUTTON,
            label: trans('resources'),
            active: 'resources' === section,
            callback: () => changeSection('resources')
          }
        ]}
      />

      {!props.loaded &&
        <ContentLoader
          size="lg"
          description={trans('history_loading', {}, 'history')}
        />
      }

      <div className="modal-body">
        {props.loaded && (isEmpty(props.results) || isEmpty(props.results[section])) &&
          <ContentPlaceholder
            size="lg"
            title={trans('workspaces' === section ? 'empty_workspaces':'empty_resources', {}, 'history')}
            help={trans('workspaces' === section ? 'empty_workspaces_help':'empty_resources_help', {}, 'history')}
          />
        }

        {props.loaded && !isEmpty(props.results) && !isEmpty(props.results[section]) &&
          <div className="d-flex flex-column gap-1">
            {props.results[section].map(result =>
              createElement(constants.RESULTS_CARD[section], {
                key: result.id,
                size: 'sm',
                direction: 'row',
                asIcon: true,
                data: result,
                primaryAction: {
                  type: LINK_BUTTON,
                  label: trans('open', {}, 'actions'),
                  target: 'workspaces' === section ? workspaceRoute(result) : resourceRoute(result),
                  onClick: props.fadeModal
                }
              })
            )}
          </div>
        }
      </div>

      <Button
        className="modal-btn"
        variant="btn"
        size="lg"
        type={LINK_BUTTON}
        label={trans('workspaces' === section ? 'all_workspaces' : 'all_resources', {}, 'history')}
        target={toolRoute('workspaces' === section ? 'workspaces' : 'resources')}
        onClick={props.fadeModal}
        exact={true}
        primary={true}
      />
    </Modal>
  )
}

HistoryModal.propTypes = {
  results: T.object,
  loaded: T.bool.isRequired,
  getHistory: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  HistoryModal
}
