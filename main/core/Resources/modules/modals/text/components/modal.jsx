import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors} from '#/main/core/modals/text/store'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

class TextModal extends Component {
  constructor(props) {
    super(props)

    this.state = {text: '', display: false}
  }

  callback(data) {
    this.props.handleSelect(data.map(data => data.id))
  }

  render() {
    return(
      <Modal
        {...this.props}
        icon="fa fa-fw fa-users"
        className="data-text-modal"
        bsSize="lg"
      >
        {!this.state.display &&
      <Fragment>
        <textarea
          rows={20}
          className={'form-control'}
          onChange={event => this.setState({text: event.target.value})}
        />
        <Button
          label={trans('search')}
          className="modal-btn btn"
          primary={true}
          type={CALLBACK_BUTTON}
          callback = {() => {
            this.props.search(this.props.fetch.url, this.state.text)
            this.setState({display: true})
          }}
        />
      </Fragment>
        }
        {this.state.display &&
        <Fragment>
          <ListData
            name={selectors.STORE_NAME}
            definition={this.props.definition}
            card={this.props.card}
            data={this.props.data}
            fetch={this.props.fetch}
            selectable={false}
            filterable={false}
            paginated={true}
            sortable={true}
          />
          <Button
            label={trans('submit')}
            className="modal-btn btn"
            primary={true}
            type={CALLBACK_BUTTON}
            callback = {() => this.callback(this.props.data)}
          />
        </Fragment>
        }
      </Modal>
    )
  }
}


TextModal.propTypes = {
  fetch: T.object,
  title: T.string,
  fadeModal: T.func.isRequired,
  search: T.func.isRequired,
  resetText: T.func.isRequired,
  definition: T.object.isRequired,
  card: T.object.isRequired,
  handleSelect: T.func.isRequired,
  data: T.array
}

export {
  TextModal
}
