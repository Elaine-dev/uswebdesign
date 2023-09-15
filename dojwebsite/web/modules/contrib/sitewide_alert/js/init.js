/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Drupal, drupalSettings, once) {
  const sitewideAlertsSelector = '[data-sitewide-alert]';

  const shouldShowOnThisPage = (pages = [], negate = true) => {
    if (pages.length === 0) {
      return true;
    }

    let pagePathMatches = false;
    const currentPath = window.location.pathname;

    for (let i = 0; i < pages.length; i++) {
      const baseUrl = drupalSettings.path.baseUrl.slice(0, -1);
      const page = baseUrl + pages[i];

      if (page.charAt(page.length - 1) === '*') {
        if (currentPath.startsWith(page.substring(0, page.length - 1))) {
          pagePathMatches = true;
          break;
        }
      } else if (page === currentPath) {
        pagePathMatches = true;
        break;
      }
    }

    return negate ? !pagePathMatches : pagePathMatches;
  };

  const alertWasDismissed = alert => {
    if (!(`alert-dismissed-${alert.uuid}` in window.localStorage)) {
      return false;
    }

    const dismissedAtTimestamp = Number(window.localStorage.getItem(`alert-dismissed-${alert.uuid}`));

    if (dismissedAtTimestamp < alert.dismissalIgnoreBefore) {
      return false;
    }

    return true;
  };

  const dismissAlert = alert => {
    window.localStorage.setItem(`alert-dismissed-${alert.uuid}`, String(Math.round(new Date().getTime() / 1000)));
    document.querySelectorAll(`[data-uuid="${alert.uuid}"]`).forEach(alert => {
      alert.dispatchEvent(new CustomEvent('sitewide-alert-dismissed', {
        bubbles: true,
        composed: true
      }));
      removeAlert(alert);
    });
  };

  const buildAlertElement = alert => {
    const alertElement = document.createElement('div');
    alertElement.innerHTML = alert.renderedAlert;

    if (alert.dismissible) {
      const dismissButtons = alertElement.getElementsByClassName('js-dismiss-button');

      for (let i = 0; i < dismissButtons.length; i++) {
        dismissButtons[i].addEventListener('click', () => dismissAlert(alert));
      }
    }

    return alertElement.firstElementChild;
  };

  const removeAlert = alert => {
    alert.dispatchEvent(new CustomEvent('sitewide-alert-removed', {
      bubbles: true,
      composed: true
    }));
    alert.remove();
  };

  const fetchAlerts = () => {
    return fetch(`${window.location.origin + drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix}sitewide_alert/load`).then(res => res.json()).then(result => result.sitewideAlerts, error => {
      console.error(error);
    });
  };

  const removeStaleAlerts = alerts => {
    const roots = document.querySelectorAll(sitewideAlertsSelector);
    roots.forEach(root => {
      const existingAlerts = root.querySelectorAll('[data-uuid]');
      const alertsToBeRemoved = Array.from(existingAlerts).filter(alert => !alerts.includes(alert.getAttribute('data-uuid')));
      alertsToBeRemoved.forEach(alert => removeAlert(alert));
    });
  };

  const initAlerts = () => {
    const roots = document.querySelectorAll(sitewideAlertsSelector);
    fetchAlerts().then(alerts => {
      removeStaleAlerts(alerts);
      alerts.forEach(alert => {
        const dismissed = alertWasDismissed(alert);
        const showOnThisPage = shouldShowOnThisPage(alert.showOnPages, alert.negateShowOnPages);
        roots.forEach(root => {
          const existingAlertElement = root.querySelector(`[data-uuid="${alert.uuid}"]`);

          if (showOnThisPage && !dismissed) {
            const renderableAlertElement = buildAlertElement(alert);
            existingAlertElement ? root.replaceChild(renderableAlertElement, existingAlertElement) : root.appendChild(renderableAlertElement);
            renderableAlertElement.dispatchEvent(new CustomEvent('sitewide-alert-rendered', {
              bubbles: true,
              composed: true
            }));
            return;
          }

          if ((dismissed || !showOnThisPage) && existingAlertElement) {
            removeAlert(existingAlertElement);
          }
        });
      });
    });
  };

  Drupal.behaviors.sitewide_alert_init = {
    attach: (context, settings) => {
      once('sitewide_alerts_init', 'html', context).forEach(element => {
        initAlerts();

        if (drupalSettings.sitewideAlert.automaticRefresh === true) {
          const interval = setInterval(() => initAlerts(), drupalSettings.sitewideAlert.refreshInterval < 1000 ? 1000 : drupalSettings.sitewideAlert.refreshInterval);

          if (!drupalSettings.sitewideAlert.automaticRefresh) {
            clearInterval(interval);
          }
        }
      });
    }
  };
})(Drupal, drupalSettings, once);