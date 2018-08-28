import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {t, tex, trans} from '#/main/core/translation'
import {API_REQUEST, url} from '#/main/app/api'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {registry} from '#/main/app/modals/registry'

import {listItemNames, getDefinition} from './../../../../items/item-types'
import {Icon} from './../../../../items/components/icon'

export const MODAL_IMPORT_ITEMS = 'MODAL_IMPORT_ITEMS'

const actions = {}

actions.getQuestions = (filter, onSuccess) => {
  let queryString = '?filters[selfOnly]=true'

  if (filter['filters'] && filter['filters']['title']) {
    const content = filter['filters']['title']
    queryString += `&filters[content]=${encodeURIComponent(content)}`
  }

  return (dispatch) => {
    dispatch({
      [API_REQUEST]: {
        url: url(['question_list']) + queryString,
        request: {
          method: 'GET'
        },
        success: (response) => onSuccess(response)
      }
    })
  }
}

class ImportItems extends Component {
  constructor(props){
    super(props)
    this.state = {
      selected: [],
      questions: [],
      total: 0,
      types: listItemNames()
    }

    this.onQuestionsRetrieved = this.onQuestionsRetrieved.bind(this)
  }

  handleSearchTextChange(value){
    if (value !== '') {
      this.getQuestions(value)
    } else {
      this.setState({
        selected: [],
        questions: [],
        total: 0
      })
    }
  }

  handleQuestionSelection(question){
    let actual = this.state.selected
    actual.push(question)
    this.setState({selected: actual})
  }

  onQuestionsRetrieved(response) {
    this.setState({questions: response.data, total: response.totalResults})
  }

  getQuestions(value){
    this.props.getQuestions({
      filters:{title: value}
    }, this.onQuestionsRetrieved)
  }

  handleClick(){
    if (this.state.selected.length > 0) {
      this.props.handleSelect(this.state.selected)
    }
    // close picker
    this.props.fadeModal()
  }

  render(){
    return(
      <Modal
        {...omit(this.props, 'handleSelect', 'getQuestions', 'questionRetrieved')}
        className="import-items-modal"
      >
        <div className="modal-body">
          <div className="form-group">
            <input
              id="searchText"
              placeholder={tex('search_by_title_or_content')}
              type="text"
              onChange={(e) => this.handleSearchTextChange(e.target.value)}
              className="form-control" />
          </div>
          {this.state.questions.length === 0 && null !== this.state.criterion && '' !== this.state.criterion &&
            <div className="text-center">
              <hr/>
              <h4>{t('no_search_results')}</h4>
            </div>
          }
        </div>
        {this.state.questions.length > 0 &&
          <table className="table table-responsive table-striped question-list-table">
            <tbody>
              {this.state.questions.map(item =>
                <tr key={item.id}>
                  <td>
                    <input name="question" type="checkbox" onClick={() => this.handleQuestionSelection(item)} />
                  </td>
                  <td>
                    <Icon name={getDefinition(item.type).name} />
                  </td>
                  <td>{item.title ? item.title : item.content }</td>
                </tr>
              )}
            </tbody>
          </table>
        }
        <button className="modal-btn btn btn-primary" disabled={this.state.selected.length === 0} onClick={this.handleClick.bind(this)}>
          {trans('import', {}, 'actions')}
        </button>
      </Modal>
    )
  }
}

ImportItems.propTypes = {
  handleSelect: T.func.isRequired,
  fadeModal: T.func.isRequired,
  getQuestions: T.func,
  questionRetrieved: T.func
}

const ImportItemsModal = connect(
  null,
  (dispatch) => ({
    getQuestions: (filter, onSuccess) => dispatch(actions.getQuestions(filter, onSuccess))
  })
)(ImportItems)

registry.add(MODAL_IMPORT_ITEMS, ImportItemsModal)

export {
  ImportItemsModal
}
