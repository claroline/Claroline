import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans, t} from '#/main/core/translation'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {select as resourceSelect} from '#/main/core/resource/selectors'

import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {actions} from '../actions'

class Categories extends Component {
  showCategoryCreationForm() {
    this.props.showModal(
      'MODAL_CATEGORY_FORM',
      {
        title: trans('create_a_category', {}, 'clacoform'),
        confirmAction: (category) => this.props.createCategory(category),
        category: {
          id: null,
          name: '',
          managers: [],
          color: '',
          notify_addition: true,
          notify_edition: true,
          notify_removal: true,
          notify_pending_comment: true
        }
      }
    )
  }

  showCategoryEditionForm(category) {
    this.props.showModal(
      'MODAL_CATEGORY_FORM',
      {
        title: trans('edit_category', {}, 'clacoform'),
        confirmAction: (cat) => this.props.editCategory(cat),
        category: {
          id: category.id,
          name: category.name,
          managers: category.managers,
          color: category.details && category.details.color ? category.details.color : '',
          notify_addition: category.details ? category.details.notify_addition : true,
          notify_edition: category.details ? category.details.notify_edition : true,
          notify_removal: category.details ? category.details.notify_removal : true,
          notify_pending_comment: category.details ? category.details.notify_pending_comment : true
        }
      }
    )
  }

  showCategoryDeletion(category) {
    this.props.showModal(MODAL_DELETE_CONFIRM, {
      title: trans('delete_category', {}, 'clacoform'),
      question: trans('delete_category_confirm_message', {name: category.name}, 'clacoform'),
      handleConfirm: () => this.props.deleteCategory(category.id)
    })
  }

  render() {
    return (
      <div>
        <h2>{trans('categories_management', {}, 'clacoform')}</h2>
        <br/>
        {this.props.canEdit ?
          <div>
            <table className="table">
              <thead>
                <tr>
                  <th>{t('name')}</th>
                  <th>{trans('managers', {}, 'clacoform')}</th>
                  <th className="text-center">{trans('addition', {}, 'clacoform')}</th>
                  <th className="text-center">{trans('edition', {}, 'clacoform')}</th>
                  <th className="text-center">{trans('removal', {}, 'clacoform')}</th>
                  <th className="text-center">{t('comment')}</th>
                  <th>{t('actions')}</th>
                </tr>
              </thead>
              <tbody>
                {this.props.categories.map((category) =>
                  <tr key={`category-${category.id}`}>
                    <td>
                      {category.details && category.details.color &&
                        <span>
                          <span className="fa fa-fw fa-circle" style={{color: category.details.color}}>
                          </span>
                          &nbsp;
                        </span>
                      }
                      {category.name}
                    </td>
                    <td>
                      {category.managers.map(manager => `${manager.firstName} ${manager.lastName}`).join(', ')}
                    </td>
                    <td className="text-center">
                      {category.details && category.details.notify_addition ?
                        <span className="fa fa-fw fa-check text-success"></span> :
                        <span className="fa fa-fw fa-times text-danger"></span>
                      }
                    </td>
                    <td className="text-center">
                      {category.details && category.details.notify_edition ?
                        <span className="fa fa-fw fa-check text-success"></span> :
                        <span className="fa fa-fw fa-times text-danger"></span>
                      }
                    </td>
                    <td className="text-center">
                      {category.details && category.details.notify_removal ?
                        <span className="fa fa-fw fa-check text-success"></span> :
                        <span className="fa fa-fw fa-times text-danger"></span>
                      }
                    </td>
                    <td className="text-center">
                      {category.details && category.details.notify_pending_comment ?
                        <span className="fa fa-fw fa-check text-success"></span> :
                        <span className="fa fa-fw fa-times text-danger"></span>
                      }
                    </td>
                    <td>
                      <button
                        className="btn btn-default btn-sm"
                        onClick={() => this.showCategoryEditionForm(category)}
                      >
                        <span className="fa fa-fw fa-pencil"></span>
                      </button>
                      &nbsp;
                      <button
                        className="btn btn-danger btn-sm"
                        onClick={() => this.showCategoryDeletion(category)}
                      >
                        <span className="fa fa-fw fa-trash"></span>
                      </button>
                    </td>
                  </tr>
                )}
              </tbody>
            </table>

            <button className="btn btn-primary" onClick={() => this.showCategoryCreationForm()}>
              <span className="fa fa-fw fa-plus"></span>
              &nbsp;
              {trans('create_a_category', {}, 'clacoform')}
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

Categories.propTypes = {
  canEdit: T.bool.isRequired,
  categories: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    managers: T.arrayOf(T.shape({
      id: T.number.isRequired,
      firstName: T.string.isRequired,
      lastName: T.string.isRequired,
      username: T.string.isRequired,
      email: T.string.isRequired,
      guid: T.string
    })),
    details: T.shape({
      color: T.string,
      notify_addition: T.boolean,
      notify_edition: T.boolean,
      notify_removal: T.boolean,
      notify_pending_comment: T.boolean
    })
  })).isRequired,
  createCategory: T.func.isRequired,
  editCategory: T.func.isRequired,
  deleteCategory: T.func.isRequired,
  showModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    canEdit: resourceSelect.editable(state),
    categories: state.categories
  }
}

function mapDispatchToProps(dispatch) {
  return {
    createCategory: (data) => dispatch(actions.createCategory(data)),
    editCategory: (data) => dispatch(actions.editCategory(data)),
    deleteCategory: (categoryId) => dispatch(actions.deleteCategory(categoryId)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  }
}

const ConnectedCategories = connect(mapStateToProps, mapDispatchToProps)(Categories)

export {ConnectedCategories as Categories}