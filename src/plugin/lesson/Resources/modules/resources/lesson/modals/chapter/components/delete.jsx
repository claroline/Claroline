import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/components/data'

// todo : don't create a form for a checkbox

class ChapterDeleteModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      data: {children: false}
    }
    this.updateProp = this.updateProp.bind(this)
    this.save = this.save.bind(this)
  }

  updateProp(propName, propValue) {
    const newData = Object.assign({}, this.state.data)
    newData[propName] = propValue

    this.setState({
      data: newData
    })
  }

  save() {
    this.props.deleteChapter(this.state.data.children)
    this.props.fadeModal()
    this.setState({
      data: {children: false}
    })
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'deleteChapter', 'chapterTitle')}
        icon="fa fa-fw fa-trash"
        title={trans('delete_warning', {}, 'lesson')}
      >
        <FormData
          level={5}
          data={this.state.data}
          setErrors={() => {}}
          updateProp={this.updateProp}
          sections={[
            {
              id: 'general',
              title: '',
              primary: true,
              fields: [
                {
                  name: 'children',
                  type: 'boolean',
                  label: trans('delete_also_children', {}, 'lesson')
                }
              ]
            }
          ]}
        />
        <button
          className="modal-btn btn btn-danger"
          onClick={this.save}
        >
          {trans('confirm')}
        </button>
      </Modal>
    )}
}

ChapterDeleteModal.propTypes = {
  'deleteChapter': T.func.isRequired,
  'chapterTitle': T.string.isRequired,
  'fadeModal': T.func.isRequired
}

export {
  ChapterDeleteModal
}