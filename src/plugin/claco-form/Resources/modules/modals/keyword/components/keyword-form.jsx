import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {Keyword as KeywordType} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {actions} from '#/plugin/claco-form/resources/claco-form/editor/store'

class KeywordFormModalComponent extends Component {
  constructor(props) {
    super(props)
    this.state = {
      isFetching : false,
      hasError: false,
      nameError: null,
      id: props.keyword.id,
      name: props.keyword.name
    }
  }

  checkKeywordName() {
    if (this.state.name) {
      this.setState({isFetching: true})

      fetch(
        url([
          'claro_claco_form_get_keyword_by_name_excluding_uuid',
          {clacoForm: this.props.clacoFormId, name: this.state.name, uuid: this.props.keyword.id}
        ]),
        {
          method: 'GET' ,
          credentials: 'include'
        }
      )
        .then(response => response.json())
        .then(results => {
          if (!results || !results.id) {
            this.registerKeyword()
            this.setState({isFetching: false})
          } else {
            this.setState({
              hasError: true,
              nameError: trans('form_not_unique_error', {}, 'clacoform'),
              isFetching: false
            })
          }
        })
    }
  }

  updateKeywordProps(property, value) {
    this.setState({[property]: value})
  }

  registerKeyword() {
    if (!this.state['hasError']) {
      this.props.saveKeyword(this.state, this.props.isNew)
      this.props.fadeModal()
    }
  }

  validateKeyword() {
    const validation = {
      hasError: false,
      nameError: null
    }

    if (!this.state['name']) {
      validation['nameError'] = trans('form_not_blank_error', {}, 'clacoform')
      validation['hasError'] = true
    }
    this.setState(validation, this.checkKeywordName)
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'isNew', 'keyword', 'clacoFormId', 'saveKeyword')}
      >
        <div className="modal-body">
          <div className={classes('form-group form-group-align row', {'has-error': this.state.nameError})}>
            <label className="control-label col-md-3">
              {trans('name')}
            </label>
            <div className="col-md-9">
              <input
                type="text"
                className="form-control"
                value={this.state.name}
                onChange={e => this.updateKeywordProps('name', e.target.value)}
              />
              {this.state.nameError &&
              <div className="help-block field-error">
                {this.state.nameError}
              </div>
              }
            </div>
          </div>
        </div>
        <div className="modal-footer">
          <button className="btn btn-default" onClick={this.props.fadeModal}>
            {trans('cancel', {}, 'actions')}
          </button>
          <button className="btn btn-primary" onClick={() => this.validateKeyword()}>
            {this.state.isFetching ?
              <span className="fa fa-fw fa-circle-o-notch fa-spin" /> :
              trans('ok')
            }
          </button>
        </div>
      </Modal>
    )
  }
}

KeywordFormModalComponent.propTypes = {
  clacoFormId:T.string.isRequired,
  isNew: T.bool.isRequired,
  keyword: T.shape(KeywordType.propTypes).isRequired,
  saveKeyword: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const KeywordFormModal = connect(
  (state) => ({
    clacoFormId: selectors.clacoForm(state).id
  }),
  (dispatch) => ({
    saveKeyword(keyword, isNew) {
      dispatch(actions.saveKeyword(keyword, isNew))
    }
  })
)(KeywordFormModalComponent)

export {
  KeywordFormModal
}