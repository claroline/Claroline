import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {implementPropTypes} from '#/main/app/prop-types'
import {trans, transChoice} from '#/main/app/intl/translation'
import {makeCancelable, url} from '#/main/app/api'
import {param} from '#/main/app/config'
import {toKey} from '#/main/core/scaffolding/text'
import {Overlay} from '#/main/app/overlays/components/overlay'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {CallbackButton} from '#/main/app/buttons/callback'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {MODAL_TAGS} from '#/plugin/tag/modals/tags'
import {Tag as TagTypes} from '#/plugin/tag/data/types/tag/prop-types'

const TagPreview = props =>
  <CallbackButton
    className="tag-preview"
    callback={props.select}
  >
    <span className="label label-info">{props.name}</span>

    {transChoice('count_elements', props.elements, {count: props.elements})}

    {props.meta.description &&
      <div className="tag-description">
        {props.meta.description}
      </div>
    }
  </CallbackButton>

implementPropTypes(TagPreview, TagTypes, {
  select: T.func.isRequired
})

const TagsList = props =>
  <div className="dropdown-menu dropdown-menu-full">
    {props.isFetching &&
      <ContentLoader />
    }

    {(!props.isFetching && 0 !== props.tags.length) &&
      <ul className="tags">
        {props.tags.map((tag) =>
          <li key={tag.id}>
            <TagPreview
              {...tag}
              select={() => props.select([tag])}
            />
          </li>
        )}
      </ul>
    }

    {props.canCreate &&
      <Button
        className="btn btn-block"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-plus"
        label={trans('create-named-tag', {tagName: props.currentTag}, 'actions')}
        callback={props.create}
        primary={true}
        disabled={props.isFetching}
      />
    }
  </div>

TagsList.propTypes = {
  currentTag: T.string,
  isFetching: T.bool,
  tags: T.arrayOf(T.shape(
    TagTypes.propTypes
  )),
  select: T.func.isRequired,
  create: T.func.isRequired,
  canCreate: T.bool.isRequired
}

TagsList.defaultProps = {
  tags: []
}

class TagInput extends Component {
  constructor(props) {
    super(props)

    this.state = {
      inputFocus: false,
      listOpened: false,
      currentTag: '',
      isFetching: false,
      results: []
    }

    this.focus = this.focus.bind(this)
    this.blur = this.blur.bind(this)
    this.close = this.close.bind(this)
    this.create = this.create.bind(this)
    this.select = this.select.bind(this)
    this.remove = this.remove.bind(this)
    this.onChange = this.onChange.bind(this)
  }

  focus() {
    this.setState({inputFocus: true})
  }

  blur() {
    this.setState({inputFocus: false})
  }

  close() {
    this.setState({listOpened: false})
  }

  create() {
    fetch(url(['apiv2_tag_create']), {
      method: 'POST' ,
      headers: new Headers({
        'Content-Type': 'application/json; charset=utf-8',
        // next header is required for symfony to recognize our requests as XMLHttpRequest
        // there is no spec about possible values, but this is the one expected by symfony
        // @see Symfony\Component\HttpFoundation\Request::isXmlHttpRequest
        'X-Requested-With': 'XMLHttpRequest'
      }),
      credentials: 'include',
      body: JSON.stringify({
        name: this.state.currentTag
      })
    })
      .then(response => response.json())
      .then(tag => {
        this.props.onChange([].concat(this.props.value || [], [tag.name]))

        this.setState({
          listOpened: false,
          currentTag: ''
        })
      })
  }

  select(tags = []) {
    const newValue = this.props.value ? this.props.value.slice() : []

    tags.map(tag => {
      if (-1 === newValue.indexOf(tag.name)) {
        newValue.push(tag.name)
      }
    })

    this.props.onChange(newValue)

    this.setState({
      listOpened: false,
      currentTag: ''
    })
  }

  remove(tagName) {
    const tagPos = this.props.value.indexOf(tagName)
    if (-1 !== tagPos) {
      const newValue = this.props.value.slice()
      newValue.splice(tagPos, 1)

      this.props.onChange(newValue)
    }
  }

  onChange(e) {
    const value = e.target.value

    this.setState({currentTag: value})

    // cancel previous search if any
    if (this.pending) {
      this.pending.cancel()
    }

    if (value && 3 <= value.length) {
      this.setState({
        listOpened: true,
        isFetching: true
      })

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

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
    }
  }

  render() {
    return (
      <div
        ref={element => this.input = element}
        className={classes('tags-control dropdown', this.props.className, {
          open: this.state.listOpened
        })}
      >
        <div className={classes('input-group', {
          [`input-group-${this.props.size}`]: !!this.props.size
        })}>
          <div className={classes('form-control', {
            focus: this.state.inputFocus
          })}>
            {this.props.value && this.props.value.map(tag =>
              <span key={toKey(tag)} className="tag label label-info">
                {tag}

                <Button
                  className="btn-link"
                  type={CALLBACK_BUTTON}
                  icon="fa fa-fw fa-times"
                  label={trans('delete', {}, 'actions')}
                  tooltip="bottom"
                  disabled={this.props.disabled}
                  callback={() => this.remove(tag)}
                  size="sm"
                />
              </span>
            )}

            <input
              type="text"
              disabled={this.props.disabled}
              value={this.state.currentTag}
              onFocus={this.focus}
              onBlur={this.blur}
              onChange={this.onChange}
            />
          </div>

          <div className="input-group-btn">
            <Button
              className="btn"
              type={MODAL_BUTTON}
              icon="fa fa-fw fa-tags"
              label={trans('add-tags', {}, 'actions')}
              tooltip="left"
              disabled={this.props.disabled}
              modal={[MODAL_TAGS, {
                selectAction: (selectedTags) => ({
                  type: CALLBACK_BUTTON,
                  callback: () => this.select(selectedTags)
                })
              }]}
            />
          </div>
        </div>

        <Overlay
          show={this.state.listOpened}
          onHide={this.close}
          rootClose={true}
          container={this.input}
          placement="bottom"
        >
          <TagsList
            currentTag={this.state.currentTag}
            isFetching={this.state.isFetching}
            tags={this.state.results}
            select={this.select}
            create={this.create}
            canCreate={param('canCreateTags')}
          />
        </Overlay>
      </div>
    )
  }
}

implementPropTypes(TagInput, DataInputTypes, {
  objectClass: T.string,
  value: T.arrayOf(T.string)
}, {
  value: []
})

export {
  TagInput
}
