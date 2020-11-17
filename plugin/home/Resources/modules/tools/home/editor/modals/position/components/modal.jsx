import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/home/tools/home/editor/modals/position/store/selectors'

const PositionModal = props => {
  const stepChoices = props.tabs
    .reduce((stepChoices, current) => Object.assign(stepChoices, {
      [current.id]: current.title
    }), {})

  // generate select actions
  const selectAction = props.selectAction(props.positionData)

  return (
    <Modal
      {...omit(props, 'tab', 'tabs', 'positionData', 'selectEnabled', 'selectAction', 'reset', 'update')}
      icon="fa fa-fw fa-arrows"
      title={trans('movement')}
      subtitle={props.tab.title}
      onEntering={() => {
        // get the current step (I don't have access to `parent` in props.step)
        const currentTab = props.tabs.find(tab => tab.id === props.tab.id)

        // convert current step position to display in form
        const currentPosition = {}

        const siblingIndex = props.tabs.findIndex(tab => tab.id === currentTab.id)
        if (1 === props.tabs.length || 0 === siblingIndex) {
          // first or only child
          currentPosition.order = 'first'
        } else if (props.tabs.length === siblingIndex + 1) {
          // last child
          currentPosition.order = 'last'
        } else {
          currentPosition.order = 'after'
          currentPosition.tab = props.tabs[siblingIndex - 1].id
        }

        props.reset(currentPosition)
      }}
    >
      <FormData
        name={selectors.STORE_NAME}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'order',
                label: trans('position'),
                type: 'choice',
                required: true,
                options: {
                  condensed: true,
                  noEmpty: true,
                  choices: isEmpty(stepChoices) ? {
                    first: trans('first')
                  } : {
                    first: trans('first'),
                    before: trans('before'),
                    after: trans('after'),
                    last: trans('last')
                  }
                },
                onChange: (order) => {
                  if (-1 !== ['first', 'last'].indexOf(order)) {
                    props.update('step', null)
                  } else if (!props.positionData.tab) {
                    // auto select a step
                    const siblings = Object.keys(stepChoices)
                    if (!isEmpty(siblings)) {
                      let step = siblings[siblings.length - 1]
                      if ('before' === order) {
                        step = siblings[0]
                      }

                      props.update('tab', step)
                    }
                  }
                },
                linked: [
                  {
                    name: 'tab',
                    label: trans('tab', {}, 'home'),
                    type: 'choice',
                    required: true,
                    hideLabel: true,
                    displayed: (position) => position.order && -1 === ['first', 'last'].indexOf(position.order),
                    options: {
                      condensed: true,
                      noEmpty: true,
                      choices: stepChoices
                    }
                  }
                ]
              }
            ]
          }
        ]}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={!props.selectEnabled}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

PositionModal.propTypes = {
  title: T.string,
  tab: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  }),
  tabs: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  })),
  positionData: T.shape({
    order: T.oneOf(['first', 'before', 'after', 'last']),
    tab: T.string
  }),
  selectEnabled: T.bool,
  selectAction: T.func.isRequired, // action generator
  reset: T.func.isRequired,
  update: T.func.isRequired,
  fadeModal: T.func.isRequired
}

PositionModal.defaultProps = {
  steps: [],
  selectEnabled: false
}

export {
  PositionModal
}
