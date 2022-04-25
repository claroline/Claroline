import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {makeCancelable, url} from '#/main/app/api'
import {currentUser} from '#/main/app/security'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {Tag as TagTypes} from '#/plugin/tag/data/types/tag/prop-types'

const TagsList = props =>
  <ul className="tags-dropdown-menu dropdown-menu">
    {props.isFetching &&
      <li className="tags-fetching text-center">
        <span className="fa fa-fw fa-circle-o-notch fa-spin" />
      </li>
    }

    {props.tags.map((tag) =>
      <li key={tag.id}>
        <Button
          type={CALLBACK_BUTTON}
          label={tag.name}
          callback={() => props.select(tag.name)}
        />
      </li>
    )}
  </ul>

TagsList.propTypes = {
  tags: T.arrayOf(T.shape(
    TagTypes.propTypes
  )),
  isFetching: T.bool.isRequired,
  select: T.func.isRequired
}

class TagTypeahead extends Component {
  constructor(props) {
    super(props)
    this.state = {
      currentTag: '',
      isFetching: false,
      results: []
    }
  }

  reset() {
    // cancel previous search if any
    if (this.pending) {
      this.pending.cancel()
    }

    this.setState({
      currentTag: '',
      isFetching: false,
      results: []
    })
  }

  updateCurrentTag(value) {
    const authenticated = currentUser()

    this.setState({currentTag: value})

    // cancel previous search if any
    if (this.pending) {
      this.pending.cancel()
    }

    if (value && 3 <= value.length) {
      this.setState({isFetching: true})

      this.pending = makeCancelable(
        fetch(
          url(['apiv2_tag_list'], {filters: {name: value, user: authenticated ? authenticated.id : null}}), {
            method: 'GET' ,
            credentials: 'include'
          })
          .then(response => response.json())
          .then(results => this.setState({results: results.data, isFetching: false}))
      )

      this.pending.promise.then(
        () => this.pending = null,
        () => this.pending = null
      )
    } else {
      this.setState({
        isFetching: false,
        results: []
      })
    }
  }

  render() {
    return (
      <div className="tag-typehead">
        {this.props.canCreate ?
          <div className="input-group">
            <input
              className="form-control"
              type="text"
              value={this.state.currentTag}
              onChange={e => this.updateCurrentTag(e.target.value)}
            />
            <span className="input-group-btn">
              <Button
                type={CALLBACK_BUTTON}
                className="btn btn-default"
                label={trans('add', {}, 'actions')}
                disabled={!this.state.currentTag.trim()}
                callback={() => {
                  this.props.select(this.state.currentTag.trim())
                  this.reset()
                }}
              />
            </span>
          </div>
          :
          <input
            className="form-control"
            type="text"
            value={this.state.currentTag}
            onChange={e => this.updateCurrentTag(e.target.value)}
          />
        }
        {(this.state.isFetching || this.state.results.length > 0) &&
          <TagsList
            isFetching={this.state.isFetching}
            tags={this.state.results}
            select={(tag) => {
              this.props.select(tag)
              this.reset()
            }}
          />
        }
      </div>
    )
  }
}

TagTypeahead.propTypes = {
  select: T.func.isRequired,
  canCreate: T.bool.isRequired
}

export {
  TagTypeahead
}
