/**
 * Download button.
 * Triggers a file download.
 */

import {registry} from '#/main/app/buttons/registry'

// gets the button component
import {DownloadButton} from '#/main/app/buttons/download/components/button'

const DOWNLOAD_BUTTON = 'download'

// make the button available for use
registry.add(DOWNLOAD_BUTTON, DownloadButton)

export {
  DOWNLOAD_BUTTON,
  DownloadButton
}
