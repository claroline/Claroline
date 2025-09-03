import React, {useCallback, useMemo, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {makeReducer, useReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {trans, transChoice} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Nav} from '#/main/app/components/nav'

import {makeListReducer, actions as listActions, selectors as listSelectors} from '#/main/app/content/list/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {LIST_TOGGLE_SELECT, LIST_TOGGLE_SELECT_ALL} from '#/main/app/content/list/store/actions'
import {constants as listConst} from '#/main/app/content/list'

/**
 * Render a picker with multiple lists in different tabs.
 */
const PickerMultipleModal = (props) => {
  const [currentTab, setCurrentTab] = useState(props.tabs[0].name)
  const currentTabList = props.tabs.find(tab => tab.name === currentTab)

  props.tabs.map(tab => {
    // append list reducer to the store if not already mounted
    const reducer = useMemo(() => makeListReducer(tab.name, {
      sortBy: tab.sortBy || null,
      filters: {filters: tab.filters || []}
    }, {
      selected: makeReducer([], {
        [makeInstanceAction(LIST_TOGGLE_SELECT, tab.name)]: (state, action) => {
          if (!props.multiple) {
            return [action.row.id]
          }

          return state
        },
        [makeInstanceAction(LIST_TOGGLE_SELECT_ALL, tab.name)]: (state, action) => {
          if (!props.multiple) {
            if (!isEmpty(action.rows)) {
              return [action.rows[0].id]
            }
          }

          return state
        }
      })
    }), [props.multiple, props.tabs.map(tab => tab.name).join('-')])
    useReducer(tab.name, reducer)
  })

  const dispatch = useDispatch()
  const reset = useCallback(() => {
    props.tabs.map(tab => {
      dispatch(listActions.resetSelect(tab.name))
      dispatch(listActions.resetFilters(tab.name, tab.filters || []))
      dispatch(listActions.invalidateData(tab.name))
    })
  }, props.tabs.map(tab => tab.name).join('-'))
  const select = useCallback((listName, row) => {
    dispatch(listActions.toggleSelect(listName, row))
  }, props.tabs.map(tab => tab.name).join('-'))

  const selected = useSelector((state) => props.tabs.reduce((acc, current) => {
    return acc.concat(listSelectors.selectedFull(listSelectors.list(state, current.name)))
  }, []))

  let selectAction
  if (props.selectAction) {
    selectAction = props.selectAction(selected)
  }

  return (
    <Modal
      size="xl"
      {...omit(props, 'name', 'definition', 'card', 'url', 'selectAction', 'displayMode')}
      className="data-picker-modal"
      onExited={reset}
      centered={true}
      scrollable={true}
      enforceFocus={true}
    >
      {1 < props.tabs.length &&
        <Nav
          className="px-4 mt-n3"
          variant="underline"
          orientation="horizontal"
          items={props.tabs.map((tab) => ({
            name: tab.name,
            label: tab.title,
            type: CALLBACK_BUTTON,
            active: currentTab === tab.name,
            callback: () => setCurrentTab(tab.name)
          }))}
        />
      }

      <div className="modal-body p-0 d-flex">
        {currentTabList &&
          <ListData
            fetch={{
              url: currentTabList.url,
              autoload: true
            }}
            className="border-top"
            autoFocus={true}
            name={currentTabList.name}
            flush={true}
            definition={currentTabList.definition}
            card={currentTabList.card}
            primaryAction={(row) => ({
              type: CALLBACK_BUTTON,
              label: trans('select', {}, 'actions'),
              callback: () => select(currentTabList.name, row)
            })}
            selectable={true}
            display={{current: currentTabList.displayMode || listConst.DISPLAY_LIST}}
          />
        }
      </div>

      <div className="modal-footer">
        {selected && 0 !== selected.length &&
          <span role="presentation">
            {transChoice('list_selected_count', selected.length, {count: selected.length}, 'platform')}
          </span>
        }

        {selectAction &&
          <Button
            label={trans('select', {}, 'actions')}
            onClick={props.fadeModal}
            {...selectAction}
            className="btn btn-primary"
            disabled={0 === selected.length}
          />
        }
      </div>
    </Modal>
  )
}

PickerMultipleModal.propTypes = {
  name: T.string.isRequired,
  fadeModal: T.func.isRequired,
  multiple: T.bool,
  tabs: T.arrayOf(T.shape({
    name: T.string.isRequired,
    filters: T.array,
    sortBy: T.shape({
      property: T.string,
      direction: T.number
    }),
    definition: T.arrayOf(T.object),
    card: T.func,
    displayMode: T.string
  })),
  children: T.any,
  selectAction: T.func
}

PickerMultipleModal.defaultProps = {
  multiple: true
}

export {
  PickerMultipleModal
}
