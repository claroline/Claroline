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

import {constants} from '#/plugin/favourite/header/favourites/constants'

const FavouritesModal = props => {
  const [section, changeSection] = useState('workspaces')

  return (
    <Modal
      {...omit(props, 'loaded', 'results', 'geFavourites', 'deleteFavourite')}
      icon="fa fa-fw fa-star"
      title={trans('favourites', {}, 'favourite')}
      onEntering={() => props.getFavourites()}
    >
      <ContentTabs
        sections={[
          {
            name: 'workspaces',
            type: CALLBACK_BUTTON,
            label: trans('workspaces'),
            target: `${props.path}/statistics/answers`,
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
          description={trans('favourites_loading', {}, 'favourite')}
        />
      }


      {props.loaded && (isEmpty(props.results) || isEmpty(props.results[section])) &&
        <ContentPlaceholder
          icon={'workspaces' === section ? 'fa fa-fw fa-book' : 'fa fa-fw fa-folder'}
          title={trans('workspaces' === section ? 'empty_workspaces':'empty_resources', {}, 'favourite')}
          help={trans('workspaces' === section ? 'empty_workspaces_help':'empty_resources_help', {}, 'favourite')}
        />
      }

      {props.loaded && !isEmpty(props.results) && !isEmpty(props.results[section]) &&
        <div className="modal-body">
          {props.results[section].map(result =>
            createElement(constants.RESULTS_CARD[section], {
              key: result.id,
              size: 'xs',
              direction: 'row',
              data: result,
              primaryAction: {
                type: LINK_BUTTON,
                label: trans('open', {}, 'actions'),
                target: 'workspaces' === section ? workspaceRoute(result) : resourceRoute(result),
                onClick: props.fadeModal
              },
              actions: [
                {
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-trash',
                  label: trans('delete', {}, 'actions'),
                  callback: () => props.deleteFavourite(result, section),
                  confirm: {
                    title: trans('delete_favorite', {}, 'favourite'),
                    subtitle: result.name,
                    message: trans('workspaces' === section ? 'delete_workspace_message' : 'delete_resource_message', {}, 'favourite')
                  },
                  dangerous: true
                }
              ]
            })
          )}
        </div>
      }

      <Button
        className="modal-btn btn"
        type={LINK_BUTTON}
        label={trans('workspaces' === section ? 'all_workspaces' : 'all_resources', {}, 'history')}
        target={toolRoute('workspaces' === section ? 'workspaces' : 'resources')}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

FavouritesModal.propTypes = {
  results: T.array,
  loaded: T.bool.isRequired,
  geFavourites: T.func.isRequired,
  deleteFavourite: T.func.isRequired,
  fadeModal: T.func.isRequired
}

FavouritesModal.defaultProps = {

}

export {
  FavouritesModal
}
