import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/core/modals/resources/store'
import {ResourceList} from '#/main/core/resource/components/list'

class ResourcesModal extends Component {
  constructor(props) {
    super(props)

    this.state = {initialized: false}
  }

  render() {
    const selectAction = this.props.selectAction(this.props.selected)

    const ownProps = [
      'root',
      'current',
      'currentDirectory',
      'selected',
      'selectAction',
      'setCurrent',
      'filters'
    ]

    return (
      <Modal
        {...omit(this.props, ownProps)}
        subtitle={this.props.currentDirectory ? this.props.currentDirectory.name : trans('all_resources', {}, 'resource')}
        onEntering={() => {
          if (this.props.current) {
            this.props.setCurrent(this.props.current, this.props.filters)
          } else if (this.props.root) {
            this.props.setCurrent(this.props.root, this.props.filters)
          }

          this.setState({initialized: true})
        }}
        className="data-picker-modal"
        size="xl"
      >
        <ResourceList
          name={`${selectors.STORE_NAME}.resources`}
          url={['apiv2_resource_list', {parent: get(this.props.currentDirectory, 'slug')}]}
          autoload={this.state.initialized}
          backAction={{
            name: 'back',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-arrow-left',
            label: get(this.props.currentDirectory, 'parent') ?
              trans('back_to', {target: get(this.props.currentDirectory, 'parent.name')}) :
              trans('back'),
            callback: () => this.props.setCurrent(get(this.props.currentDirectory, 'parent'), this.props.filters),
            disabled: isEmpty(this.props.currentDirectory) || (this.props.root && this.props.currentDirectory.slug === this.props.root.slug)
          }}
          primaryAction={(resourceNode) => {
            if ('directory' === resourceNode.meta.type) {
              return ({
                type: CALLBACK_BUTTON,
                callback: () => this.props.setCurrent(resourceNode, this.props.filters)
              })
            }

            return null
          }}
          actions={undefined}
        />

        <Button
          label={trans('select', {}, 'actions')}
          {...selectAction}
          className="modal-btn"
          variant="btn"
          size="lg"
          primary={true}
          disabled={0 === this.props.selected.length || !this.state.initialized}
          onClick={this.props.fadeModal}
        />
      </Modal>
    )
  }
}

ResourcesModal.propTypes = {
  // from props
  filters: T.array,
  root: T.shape({
    slug: T.string.isRequired,
    name: T.string.isRequired
  }),
  current: T.shape({
    slug: T.string.isRequired,
    name: T.string.isRequired
  }),
  selectAction: T.func.isRequired, // action generator for the select button

  // from store
  selected: T.array.isRequired,
  currentDirectory: T.shape({
    slug: T.string.isRequired,
    name: T.string.isRequired
  }),
  setCurrent: T.func.isRequired,
  // from modal
  fadeModal: T.func.isRequired
}

ResourcesModal.defaultProps = {
  icon: 'fa fa-fw fa-folder',
  title: trans('resources'),
  filters: [],
  current: null
}

export {
  ResourcesModal
}
