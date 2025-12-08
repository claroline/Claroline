import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/home/tools/home/editor/modals/position/store/selectors'
import {FormModal} from '#/main/app/data/modals/form'

const PositionModal = props => {
  const parentChoices = props.tabs
    .filter(tab => tab.id !== props.tab.id)
    .reduce((tabChoices, current) => Object.assign(tabChoices, {
      [current.id]: current.title
    }), {})

  const stepChoices = props.tabs
    // only display the sub steps of the selected `parent`
    .filter(tab => tab.id !== props.tab.id && get(tab, 'parent.id') === props.positionData.parent)
    .reduce((tabChoices, current) => Object.assign(tabChoices, {
      [current.id]: current.title
    }), {})

  // get the current step (I don't have access to `parent` in props.tab)
  const currentTab = props.tabs.find(tab => tab.id === props.tab.id)

  // convert current step position to display in form
  const currentPosition = {}

  // get parent
  if (currentTab.parent) {
    currentPosition.parent = currentTab.parent.id
  }

  // get position between current parent children
  const siblings = props.tabs.filter(tab => get(tab, 'parent.id') === get(currentTab, 'parent.id'))
  const siblingIndex = siblings.findIndex(tab => tab.id === currentTab.id)
  if (1 === siblings.length || 0 === siblingIndex) {
    // first or only child
    currentPosition.order = 'first'
  } else if (siblings.length === siblingIndex + 1) {
    // last child
    currentPosition.order = 'last'
  } else {
    currentPosition.order = 'after'
    currentPosition.tab = siblings[siblingIndex - 1].id
  }

  return (
    <FormModal
      {...omit(props, 'tab', 'tabs', 'positionData', 'update')}
      icon="fa fa-fw fa-arrows"
      title={trans('movement')}
      name={selectors.STORE_NAME}
      data={currentPosition}
      isNew={false}
      subtitle={props.tab.title}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'parent',
              label: trans('parent'),
              type: 'choice',
              placeholder: trans('root'),
              options: {
                condensed: true,
                choices: parentChoices
              },
              onChange: () => {
                props.update('order', 'last')
                props.update('tab', null)
              }
            }, {
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
                  props.update('tab', null)
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
  // from form
  positionData: T.shape({
    parent: T.string,
    order: T.oneOf(['first', 'before', 'after', 'last']),
    tab: T.string
  }),
  update: T.func.isRequired,
  // from modal
  fadeModal: T.func.isRequired
}

export {
  PositionModal
}
