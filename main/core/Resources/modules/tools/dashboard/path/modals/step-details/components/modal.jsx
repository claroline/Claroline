import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {
  Table,
  TableHeaderCell,
  TableRow,
  TableCell
} from '#/main/core/layout/table/components/table'

const StepDetailsModal = props =>
  <Modal
    {...omit(props, 'users')}
    icon="fa fa-fw fa-user"
    title={trans('users')}
  >
    <div className="modal-body">
      <Table className="data-table">
        <thead>
          <TableRow>
            <TableHeaderCell align={'left'}>
              {trans('first_name')}
            </TableHeaderCell>
            <TableHeaderCell align={'left'}>
              {trans('last_name')}
            </TableHeaderCell>
          </TableRow>
        </thead>
        <tbody>
          {props.users.map((user,idx) =>
            <TableRow key={idx}>
              <TableCell align={'left'}>
                {user.firstName}
              </TableCell>
              <TableCell align={'left'}>
                {user.lastName}
              </TableCell>
            </TableRow>
          )}
        </tbody>
      </Table>
    </div>
  </Modal>

StepDetailsModal.propTypes = {
  users: T.arrayOf(T.shape({
    id: T.string,
    username: T.string,
    firstName: T.string,
    lastName: T.string,
    name: T.string
  }))
}

StepDetailsModal.defaultProps = {
  users: []
}

export {
  StepDetailsModal
}
