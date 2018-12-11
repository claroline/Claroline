import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {Button} from '#/main/app/action/components/button'

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
          {...props.selectAction(tag.name)}
          label={tag.name}
        />
      </li>
    )}
  </ul>

TagsList.propTypes = {
  tags: T.arrayOf(T.shape(
    TagTypes.propTypes
  )),
  isFetching: T.bool.isRequired,
  selectAction: T.func.isRequired
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

  updateCurrentTag(value) {
    this.setState({currentTag: value})

    if (value) {
      this.setState({isFetching: true})

      fetch(url(['apiv2_tag_list'], {filters: {name:value}}), {
        method: 'GET' ,
        credentials: 'include'
      })
        .then(response => response.json())
        .then(results => this.setState({results: results.data, isFetching: false}))
    } else {
      this.setState({results: [], isFetching: false})
    }
  }

  render() {
    const selectAction = this.props.selectAction(this.state.currentTag.trim())

    return (
      <div className="tag-typehead">
        <div className="input-group">
          <input
            className="form-control"
            type="text"
            value={this.state.currentTag}
            onChange={e => this.updateCurrentTag(e.target.value)}
          />
          <span className="input-group-btn">
            <Button
              {...selectAction}
              className="btn btn-default"
              disabled={selectAction.disabled || !this.state.currentTag.trim()}
            />
          </span>
        </div>

        {(this.state.isFetching || this.state.results.length > 0) &&
          <TagsList
            isFetching={this.state.isFetching}
            tags={this.state.results}
            selectAction={this.props.selectAction}
          />
        }
      </div>
    )
  }
}

TagTypeahead.propTypes = {
  // an action generator
  selectAction: T.func.isRequired
}

export {
  TagTypeahead
}
