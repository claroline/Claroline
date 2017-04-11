import {tex} from '#/main/core/translation'
import select from './selectors'

export function makeSaveGuard(getState) {
  // no need to attach a listener if the quiz is readonly
  if (select.editor(getState())) {
    window.addEventListener('beforeunload', e => {
      if (!select.saved(getState())) {
        // note: this is supposed to be the text displayed in the browser built-in
        // popup (see https://developer.mozilla.org/en-US/docs/Web/API/WindowEventHandlers/onbeforeunload#Example)
        // but it doesn't seem to be actually used in modern browsers. We use it
        // here because a string is needed anyway.
        e.returnValue = tex('unsaved_changes_warning')
        return e.returnValue
      }
    })
  }
}
