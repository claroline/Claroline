import React, {Component, PropTypes as T} from 'react'
import classes from 'classnames'
import {t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/fos-js-router'
import cloneDeep from 'lodash/cloneDeep'

const UsersList = props =>
  <ul className="dropdown-menu">
    {props.users.map((user) =>
      <li key={user.id}>
        <a
          role="button"
          href=""
          onClick={(e) => {
            props.select(user)
            e.preventDefault()
          }}
        >
          {user.firstName} {user.lastName}
        </a>
      </li>
    )}
  </ul>

UsersList.propTypes = {
  users: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })).isRequired,
  select: T.func.isRequired
}

const SelectedUser = props => {

  return (
    <span style={{display: 'inline-block'}}>
      <span className="selected-user label label-success">
        {props.user.firstName} {props.user.lastName}

        <button type="button" className="btn btn-link" onClick={props.remove}>
          <span className="fa fa-times" />
          <span className="sr-only">{t('list_remove_filter')}</span>
        </button>
      </span>
    </span>
  )
}

SelectedUser.propTypes = {
  user: T.array.isRequired,
  remove: T.func.isRequired
}

export class UserTypeahead extends Component {
  constructor(props) {
    super(props)

    this.state = {
      isFetching: false,
      searchString: '',
      results: [],
      selected: cloneDeep(props.selected)
    }
  }

  search(search) {
    this.setState({searchString: search})

    if (search && search.length > 2) {
      this.setState({isFetching: true})

      fetch(generateUrl('api_get_search_users', {limit: 20, page: 0}) + '?name[]=' + search, {
        method: 'GET' ,
        credentials: 'include'
      })
      .then(response => response.json())
      .then(results => this.setState({results: results.users, isFetching: false}))
    }
  }

  selectUser(user) {
    this.props.handleSelect(user)

    this.setState({
      searchString: '',
      results: [],
      selected: this.state.selected.concat([user])
    })

  }

  removeUser(user) {
    this.props.handleRemove(user)

    const selected = this.state.selected
    selected.splice(selected.findIndex(toRemove => toRemove.id === user.id), 1)
    this.setState({selected})
  }

  render() {
    return (
      <div>
        <div className={classes('dropdown', 0 < this.state.results.length ? 'open' : null)}>
          <div className="input-group">
            <span className="input-group-addon">
              <span
                className={classes(
                  'fa fa-fw',
                  this.state.isFetching ? 'fa-circle-o-notch fa-spin' : 'fa-user'
                )}
              ></span>
            </span>
            <input
              id="search-users"
              type="text"
              className="form-control"
              value={this.state.searchString}
              onChange={e => this.search(e.target.value)}
            />

          </div>
          <UsersList
            users={this.state.results}
            select={this.selectUser.bind(this)}
          />
        </div>
        <span className="typeahead-user-selected">
          {this.state.selected.map(user =>
            <SelectedUser
              user={user}
              remove={() => this.removeUser(user)}
            />
          )}
        </span>
      </div>
    )
  }
}

UserTypeahead.propTypes = {
  handleSelect: T.func.isRequired,
  handleRemove: T.func,
  selected: T.array.isRequired
}

UserTypeahead.defaultProps = {
  selected: []
}
