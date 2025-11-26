import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/main/core/modals/resources/store'
import {ResourceList} from '#/main/core/resource/components/list'
import {ModalTabbed} from '#/main/app/overlays/modal/components/tabbed'
import {actions as listActions} from '#/main/app/content/list'

const ResourcesModal = (props) => {
  const dispatch = useDispatch()

  const [initialized, setInitialized] = useState(false)
  const [section, changeSection] = useState(props.contextId ? 'current': 'all')
  const selectAction = props.selectAction(props.selected)

  const ownProps = [
    'contextId',
    'root',
    'current',
    'currentDirectory',
    'selected',
    'selectAction',
    'setCurrent',
    'filters',
    'reset'
  ]

  return (
    <ModalTabbed
      {...omit(props, ownProps)}
      onEntering={() => {
        if (props.current) {
          props.setCurrent(props.current, props.filters)
          dispatch(listActions.resetFilters(selectors.LIST_NAME, props.filters || []))
        }

        setInitialized(true)
      }}
      onExited={props.reset}
      className="data-picker-modal"
      size="xl"
      tabs={[
        {
          name: 'current',
          type: CALLBACK_BUTTON,
          label: trans('Resources de l\'espace'),
          active: 'current' === section,
          callback: () => {
            props.setCurrent(null, props.filters)
            changeSection('current')
          },
          displayed: !!props.contextId
        }, {
          name: 'all',
          type: CALLBACK_BUTTON,
          label: trans('Toutes les resources'),
          active: 'all' === section,
          callback: () => {
            props.setCurrent(null, props.filters)
            changeSection('all')
          }
        }
      ]}
    >
      <ResourceList
        name={selectors.LIST_NAME}
        url={'current' === section ?
          ['apiv2_resource_list', {contextId: props.contextId, parent: get(props.currentDirectory, 'slug')}] :
          ['apiv2_resource_list', props.currentDirectory ? {contextId: get(props.currentDirectory, 'workspace.id'), parent: get(props.currentDirectory, 'slug')} : {}]
        }
        flush={true}
        autoFocus={true}
        autoload={initialized}
        backAction={{
          name: 'back',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-arrow-left',
          label: get(props.currentDirectory, 'parent') ?
            trans('back_to', {target: get(props.currentDirectory, 'parent.name')}) :
            trans('back'),
          callback: () => props.setCurrent(get(props.currentDirectory, 'parent'), props.filters),
          disabled: isEmpty(props.currentDirectory) || (props.root && props.currentDirectory.slug === props.root.slug)
        }}
        primaryAction={(resourceNode) => {
          if ('directory' === resourceNode.meta.type) {
            return ({
              type: CALLBACK_BUTTON,
              callback: () => props.setCurrent(resourceNode, props.filters)
            })
          }

          return null
        }}
        actions={undefined}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn"
        variant="btn"
        size="lg"
        primary={true}
        disabled={0 === props.selected.length || !initialized}
        onClick={props.fadeModal}
      />
    </ModalTabbed>
  )
}

ResourcesModal.propTypes = {
  // from props
  filters: T.array,
  contextId: T.string,
  root: T.shape({
    slug: T.string.isRequired,
    name: T.string.isRequired
  }),
  current: T.shape({
    slug: T.string.isRequired,
    name: T.string.isRequired
  }),
  selectAction: T.func.isRequired, // action generator for the select button

  // from store
  selected: T.array.isRequired,
  currentDirectory: T.shape({
    slug: T.string.isRequired,
    name: T.string.isRequired
  }),
  setCurrent: T.func.isRequired,
  reset: T.func.isRequired,
  // from modal
  fadeModal: T.func.isRequired
}

ResourcesModal.defaultProps = {
  icon: 'fa fa-fw fa-folder',
  title: trans('resources'),
  filters: [],
  current: null
}

export {
  ResourcesModal
}
