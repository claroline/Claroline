import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {HelpBlock} from '#/main/core/layout/form/components/help-block.jsx'

// todo : finish implementation
// todo : replace in IPList control
// todo : replace in quiz tag picking

const Item = props =>
  <div className="item-control">
    <select
      className="form-control input-sm"
      onChange={tag => props.onChange([tag, props.value[1]])}
    >
      <option value="">{t('quiz_select_picking_tags')}</option>
      {props.tags.map(tag =>
        <option value={tag} selected={tag === props.value[0]}>{tag}</option>
      )}
    </select>
    <input
      type="number"
      min="1"
      className="form-control input-sm"
      onChange={e => props.onChange([props.value[0], e.target.value])}
      style={{maxWidth: '80px'}}
    />
  </div>

Item.propTypes = {
  tags: T.arrayOf(T.string),
  value: T.array.isRequired,
  onChange: T.func.isRequired
}

class ItemList extends Component {
  constructor(props) {
    super(props)

    this.state = {
      pending: ['', 1] // first: tag name / second: nb of questions
    }

    this.addItem       = this.addItem.bind(this)
    this.updateItem    = this.updateItem.bind(this)
    this.updatePending = this.updatePending.bind(this)
    this.removeItem    = this.removeItem.bind(this)
    this.removeAll     = this.removeAll.bind(this)
  }

  addItem() {
    const newItems = this.props.items.slice()

    newItems.push(this.state.pending)

    this.updatePending(['', 1])

    this.props.onChange(newItems)
  }

  updatePending(newItem) {
    this.setState({
      pending: newItem
    })
  }

  updateItem(index, newItem) {
    const newItems = this.props.items.slice()

    // update
    newItems[index] = newItem

    this.props.onChange(newItems)
  }

  removeItem(index) {
    const newItems = this.props.items.slice()

    // remove
    newItems.splice(index, 1)

    this.props.onChange(newItems)
  }

  removeAll() {
    this.props.onChange([])
  }

  render() {
    return (
      <div id={this.props.id} className="tag-list-control">
        <div className="tag-item tag-add">
          <Item
            id={`${this.props.id}-add`}
            value={this.state.pending}
            onChange={this.updatePending}
          />

          <TooltipButton
            id={`${this.props.id}-add-btn`}
            title={t('add')}
            className="btn-link"
            disabled={!this.state.pending[0] || !this.state.pending[1]}
            onClick={this.addItem}
          >
            <span className="fa fa-fw fa-plus" />
          </TooltipButton>
        </div>

        {this.props.help &&
          <HelpBlock help={this.props.help} />
        }

        {0 !== this.props.items.length &&
          <button
            type="button"
            className="btn btn-sm btn-link-danger"
            onClick={this.removeAll}
          >
            {t('delete_all')}
          </button>
        }

        {0 !== this.props.items.length &&
          <ul>
            {this.props.items.map((item, index) =>
              <li key={`${this.props.id}-${index}`} className="tag-item">
                <Item
                  id={`${this.props.id}-auth-${index}`}
                  value={item}
                  onChange={item => this.updateItem(index, item)}
                />

                <TooltipButton
                  id={`${this.props.id}-auth-${index}-delete`}
                  title={t('delete')}
                  className="btn-link-danger"
                  onClick={() => this.removeItem(index)}
                >
                  <span className="fa fa-fw fa-trash-o" />
                </TooltipButton>
              </li>
            )}
          </ul>
        }

        {0 === this.props.items.length &&
          <div className="no-item-info">{this.props.emptyText}</div>
        }
      </div>
    )
  }
}

ItemList.propTypes = {
  id: T.string.isRequired,
  items: T.array.isRequired,
  onChange: T.func.isRequired,
  children: T.element.isRequired, // the form control of the item
  help: T.string,
  emptyText: T.string
}

ItemList.defaultProps = {
  emptyText: t('no_item')
}

export {
  ItemList
}
