import React, {Component, PropTypes as T} from 'react'
import Modal from 'react-bootstrap/lib/Modal'
import {connect} from 'react-redux'
import {listItemNames, getDefinition} from './../../../items/item-types'
import {Icon} from './../../../items/components/icon.jsx'
import {t, tex, trans} from './../../../utils/translate'
import {BaseModal} from './../../../modal/components/base.jsx'
import {REQUEST_SEND} from './../../../api/actions'


export const MODAL_IMPORT_ITEMS = 'MODAL_IMPORT_ITEMS'
const actions = {}

actions.getQuestions = (filter, onSuccess) => {
  return (dispatch) => {
    dispatch({
      [REQUEST_SEND]: {
        route: ['question_search'],
        request: {
          method: 'POST' ,
          body: JSON.stringify(filter)
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
    this.setState({questions: response.questions, total: response.total})
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

  getTypeName(mimeType){
    const type = this.state.types.find(type => type.type === mimeType)
    return undefined !== type ? trans(type.name, {}, 'question_types'): t('error')
  }

  render(){
    return(
      <BaseModal {...this.props} className="import-items-modal">
        <Modal.Body>
          <div className="form-group">
            <input
              id="searchText"
              placeholder={tex('search_by_title_or_content')}
              type="text"
              onChange={(e) => this.handleSearchTextChange(e.target.value)}
              className="form-control" />
          </div>
          { this.state.questions.length === 0 && null !== this.state.criterion && '' !== this.state.criterion &&
            <div className="text-center">
              <hr/>
              <h4>{t('no_search_results')}</h4>
            </div>
          }
        </Modal.Body>
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
        <Modal.Footer>
          <button className="btn btn-default" onClick={this.props.fadeModal}>
            {t('cancel')}
          </button>
          <button className="btn btn-primary" disabled={this.state.selected.length === 0} onClick={this.handleClick.bind(this)}>
            {t('ok')}
          </button>
        </Modal.Footer>
      </BaseModal>
    )
  }
}

ImportItems.propTypes = {
  handleSelect: T.func.isRequired,
  fadeModal: T.func.isRequired,
  getQuestions: T.func,
  questionRetrieved: T.func
}

function mapDispatchToProps(dispatch) {
  return {
    getQuestions: (filter, onSuccess) => dispatch(actions.getQuestions(filter, onSuccess))
  }
}

export const ImportItemsModal = connect(undefined, mapDispatchToProps)(ImportItems)
