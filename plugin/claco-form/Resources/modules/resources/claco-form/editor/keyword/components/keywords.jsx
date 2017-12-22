import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans, t} from '#/main/core/translation'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {select as resourceSelect} from '#/main/core/layout/resource/selectors'
import {actions} from '../actions'

class Keywords extends Component {
  showKeywordCreationForm() {
    this.props.showModal(
      'MODAL_KEYWORD_FORM',
      {
        title: trans('create_a_keyword', {}, 'clacoform'),
        confirmAction: (keyword) => this.props.createKeyword(keyword),
        keyword: {
          id: 0,
          name: ''
        },
        resourceId: this.props.resourceId
      }
    )
  }

  showKeywordEditionForm(keyword) {
    this.props.showModal(
      'MODAL_KEYWORD_FORM',
      {
        title: trans('edit_keyword', {}, 'clacoform'),
        confirmAction: (k) => this.props.editKeyword(k),
        keyword: {
          id: keyword.id,
          name: keyword.name
        },
        resourceId: this.props.resourceId
      }
    )
  }

  showKeywordDeletion(keyword) {
    this.props.showModal(MODAL_DELETE_CONFIRM, {
      title: trans('delete_keyword', {}, 'clacoform'),
      question: trans('delete_keyword_confirm_message', {name: keyword.name}, 'clacoform'),
      handleConfirm: () => this.props.deleteKeyword(keyword.id)
    })
  }

  render() {
    return (
      <div>
        <h2>{trans('keywords_management', {}, 'clacoform')}</h2>
        <br/>
        {this.props.canEdit ?
          <div>
            <table className="table">
              <thead>
                <tr>
                  <th>{t('name')}</th>
                  <th>{t('actions')}</th>
                </tr>
              </thead>
              <tbody>
                {this.props.keywords.map((keyword) =>
                  <tr key={`keyword-${keyword.id}`}>
                    <td>
                      {keyword.name}
                    </td>
                    <td>
                      <button
                        className="btn btn-default btn-sm"
                        onClick={() => this.showKeywordEditionForm(keyword)}
                      >
                        <span className="fa fa-w fa-pencil" />
                      </button>
                      &nbsp;
                      <button
                        className="btn btn-danger btn-sm"
                        onClick={() => this.showKeywordDeletion(keyword)}
                      >
                        <span className="fa fa-w fa-trash" />
                      </button>
                    </td>
                  </tr>
                )}
              </tbody>
            </table>

            <button className="btn btn-primary" onClick={() => this.showKeywordCreationForm()}>
              <span className="fa fa-w fa-plus" />
              &nbsp;
              {trans('create_a_keyword', {}, 'clacoform')}
            </button>
          </div> :
          <div className="alert alert-danger">
            {t('unauthorized')}
          </div>
        }
      </div>
    )
  }
}

Keywords.propTypes = {
  canEdit: T.bool.isRequired,
  resourceId: T.number.isRequired,
  keywords: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired
  })).isRequired,
  createKeyword: T.func.isRequired,
  editKeyword: T.func.isRequired,
  deleteKeyword: T.func.isRequired,
  showModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    canEdit: resourceSelect.editable(state),
    resourceId: state.resource.id,
    keywords: state.keywords
  }
}

function mapDispatchToProps(dispatch) {
  return {
    createKeyword: (data) => dispatch(actions.createKeyword(data)),
    editKeyword: (data) => dispatch(actions.editKeyword(data)),
    deleteKeyword: (keywordId) => dispatch(actions.deleteKeyword(keywordId)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  }
}

const ConnectedKeywords = connect(mapStateToProps, mapDispatchToProps)(Keywords)

export {ConnectedKeywords as Keywords}