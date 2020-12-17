import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {
  Table,
  TableHeaderCell,
  TableRow,
  TableCell
} from '#/main/app/content/components/table'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {MODAL_MESSAGE} from '#/plugin/message/modals/message'

class StepDetailsModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      selection: []
    }
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'users', 'sendMessage')}
        icon="fa fa-fw fa-user"
        title={trans('users')}
      >
        <div className="modal-body">
          <Table className="data-table">
            <thead>
              <TableRow>
                <TableHeaderCell align={'left'}>
                  <input
                    type="checkbox"
                    id="checkbox-all"
                    onChange={e => {
                      if(e.target.checked) {
                        this.setState({selection: this.props.users})
                      } else {
                        this.setState({selection: []})
                      }
                    }}
                  />
                </TableHeaderCell>
                <TableHeaderCell align={'left'}>
                  {trans('first_name')}
                </TableHeaderCell>
                <TableHeaderCell align={'left'}>
                  {trans('last_name')}
                </TableHeaderCell>
              </TableRow>
            </thead>
            <tbody>
              {this.props.users.map((user) =>
                <TableRow key={`table-row-${user.id}`}>
                  <TableCell align={'left'}>
                    <input
                      type="checkbox"
                      id="checkbox-all"
                      checked={-1 < this.state.selection.findIndex(u => u.id === user.id)}
                      onChange={e => {
                        const newSelection = cloneDeep(this.state.selection)
                        const index = newSelection.findIndex(u => u.id === user.id)

                        if (e.target.checked && -1 === index) {
                          newSelection.push(user)
                        } else if (!e.target.checked && -1 < index) {
                          newSelection.splice(index, 1)
                        }
                        this.setState({selection: newSelection})
                      }}
                    />
                  </TableCell>
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

          <ModalButton
            className="btn"
            style={{marginTop: 10}}
            primary={true}
            disabled={0 === this.state.selection.length}
            modal={[MODAL_MESSAGE, {
              receivers: {users: this.state.selection}
            }]}
          >
            <span className="fa fa-fw fa-envelope icon-with-text-right" />
            {trans('send-message', {}, 'actions')}
          </ModalButton>
        </div>
      </Modal>
    )
  }
}

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
