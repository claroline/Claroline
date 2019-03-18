import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {FormGroup} from '#/main/app/content/form/components/group'

import {update} from '#/plugin/exo/utils/utils' // TODO : remove me
import {UserTypeahead} from '#/plugin/exo/users/components/typeahead'

// TODO : use core UserTypeahead

const SelectedUsers = props =>
  <ul className="list-group">
    {props.users.map((user) =>
      <li key={`selected-${user.id}`} className="list-group-item">
        {user.name}
        <button
          type="button"
          className="btn btn-link btn-sm"
          onClick={() => props.deselect(user)}
        >
          <span className="fa fa-fw fa-times" />
        </button>
      </li>
    )}
  </ul>

SelectedUsers.propTypes = {
  users: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })).isRequired,
  deselect: T.func.isRequired
}

class SharingModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      adminRights: false,
      users: []
    }
  }

  selectUser(user) {
    this.setState(update(this.state, {
      users: {$push: [user]}
    }))
  }

  deselectUser(user) {
    this.setState(update(this.state, {
      users: {$splice: [[this.state.users.indexOf(user), 1]]}
    }))
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'handleShare')}
        className="share-modal"
      >
        <div className="modal-body">
          <div className="checkbox">
            <label htmlFor="share-admin-rights">
              <input
                id="share-admin-rights"
                type="checkbox"
                name="share-admin-rights"
                checked={this.state.adminRights}
                onChange={() => this.setState({
                  adminRights: !this.state.adminRights
                })}
              />
              {trans('share_admin_rights', {}, 'quiz')}
            </label>
          </div>

          <FormGroup
            id="share-users"
            label={trans('share_with', {}, 'quiz')}
          >
            <UserTypeahead
              handleSelect={this.selectUser.bind(this)}
            />
          </FormGroup>

          {0 < this.state.users.length &&
          <SelectedUsers
            users={this.state.users}
            deselect={this.deselectUser.bind(this)}
          />
          }
        </div>

        <button
          className="modal-btn btn btn-primary"
          disabled={0 === this.state.users.length}
          onClick={() => this.props.handleShare(this.state.users, this.state.adminRights)}
        >
          {trans('share', {}, 'actions')}
        </button>
      </Modal>
    )
  }
}

SharingModal.propTypes = {
  handleShare: T.func.isRequired
}

export {
  SharingModal
}
