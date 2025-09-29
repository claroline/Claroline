import React, {useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {useReducer} from '#/main/app/store/reducer'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays'
import {ListData, makeListReducer} from '#/main/app/content/list'

const STORE_NAME = 'viewersModal'

const ViewersModal = (props) => {
  const reducer = useMemo(() => makeListReducer(STORE_NAME, {
    sortBy: {property: 'seenAt', direction: -1}
  }), [STORE_NAME])
  useReducer(STORE_NAME, reducer)

  return (
    <Modal
      title={trans('viewers')}
      {...omit(props, 'url')}
      className="data-picker-modal"
      centered={true}
      scrollable={true}
      enforceFocus={true}
    >
      <div className="modal-body p-0 d-flex">
        <ListData
          fetch={{
            url: props.url,
            autoload: true
          }}
          className="border-top"
          autoFocus={true}
          name={STORE_NAME}
          flush={true}
          selectable={false}
          definition={[
            {
              name: 'user',
              type: 'user',
              label: trans('user'),
              displayed: true,
              filterable: true,
              sortable: true
            }, {
              name: 'seenAt',
              type: 'date',
              label: trans('date'),
              options: {time: true},
              displayed: true,
              filterable: true,
              sortable: true
            }, {
              name: 'count',
              type: 'number',
              label: trans('views'),
              displayed: true,
              filterable: true,
              sortable: true
            }
          ]}
        />
      </div>
    </Modal>
  )
}

ViewersModal.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired
}


export {
  ViewersModal
}
