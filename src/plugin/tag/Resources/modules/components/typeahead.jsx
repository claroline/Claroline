import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {makeCancelable, url} from '#/main/app/api'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {Tag as TagTypes} from '#/plugin/tag/data/types/tag/prop-types'

const TagsList = props =>
  <div className="tags-dropdown-menu dropdown-menu">
    {props.isFetching &&
      <div className="tags-fetching text-center">
        <span className="fa fa-fw fa-circle-notch fa-spin" />
      </div>
    }

    {props.tags.map((tag) =>
      <Button
        key={tag.id}
        className="dropdown-item"
        type={CALLBACK_BUTTON}
        label={tag.name}
        callback={() => props.select(tag.name)}
      />
    )}
  </div>

TagsList.propTypes = {
  tags: T.arrayOf(T.shape(
    TagTypes.propTypes
  )),
  isFetching: T.bool.isRequired,
  select: T.func.isRequired
}

/**
 * @deprecated
 */
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
    this.setState({currentTag: value})

    // cancel previous search if any
    if (this.pending) {
      this.pending.cancel()
    }

    if (value && 3 <= value.length) {
      this.setState({isFetching: true})

      this.pending = makeCancelable(
        fetch(
          url(['apiv2_tag_list'], {filters: {name: value}}), {
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
