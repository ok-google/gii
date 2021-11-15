function convertDateToSQL(obj){

  var newSD = new Date(obj);
  var year = newSD.getFullYear();
  var month = ((parseInt(newSD.getMonth())+1).toString().length == 1 ? "0"+(parseInt(newSD.getMonth())+1) : (parseInt(newSD.getMonth())+1));
  var date = (newSD.getDate().toString().length == 1 ? "0"+newSD.getDate() : newSD.getDate());
  return year+"-"+month+"-"+date;
}

function deleteConfirmation(delete_url, quickRedirectBack = false) {
  Swal.fire({
    title: 'Are you sure?',
    type: 'warning',
    showCancelButton: true,
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    backdrop: false,
  }).then(result => {
    if (result.value) {
      Swal.fire({
        title: 'Deleting...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        backdrop: false,
        onOpen: () => {
          Swal.showLoading()
        }
      })
      $.ajax({
        url: delete_url,
        type: 'DELETE'
      }).then( response => {
        Swal.fire({
          title: 'Deleted!',
          text: 'Your data has been deleted.',
          type: 'success',
          backdrop: false,
        }).then(() => {
          if (quickRedirectBack) {
            redirect('back()')
          }
          if (objHasProp(response, 'data.redirect_to')) {
            redirect(response.data.redirect_to);
          }
        })
      })
      .catch(error => {
        Swal.fire('Error!',`${error.statusText}`,'error')
      });
    }
  });
}

function restoreConfirmation(restore_url) {
  Swal.fire({
    title: 'Are you sure?',
    type: 'warning',
    showCancelButton: true,
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    backdrop: false,
  }).then(result => {
    if (result.value) {
      Swal.fire({
        title: 'Restoring...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        backdrop: false,
        onOpen: () => {
          Swal.showLoading()
        }
      })
      $.ajax({
        url: restore_url,
        type: 'GET'
      }).then( response => {
        Swal.fire({
          title: 'Restored!',
          text: 'Your data has been restored.',
          type: 'success',
          backdrop: false,
        }).then(() => {
          if (objHasProp(response, 'data.redirect_to')) {
            redirect(response.data.redirect_to);
          }
        })
      })
      .catch(error => {
        Swal.fire('Error!',`${error.statusText}`,'error')
      });
    }
  });
}

function saveConfirmation(save_url) {
  Swal.fire({
    title: 'Are you sure?',
    type: 'warning',
    showCancelButton: true,
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    backdrop: false,
  }).then(result => {
    if (result.value) {
      Swal.fire({
        title: 'Saving...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        backdrop: false,
        onOpen: () => {
          Swal.showLoading()
        }
      })
      $.ajax({
        url: save_url,
        type: 'GET'
      }).then( response => {
        Swal.fire({
          title: 'Saved!',
          text: 'Your data has been saved.',
          type: 'success',
          backdrop: false,
        }).then(() => {
          if (objHasProp(response, 'data.redirect_to')) {
            redirect(response.data.redirect_to);
          }
        })
      })
      .catch(error => {
        Swal.fire('Error!',`${error.statusText}`,'error')
      });
    }
  });
}

function saveConfirmation2(save_url) {
  Swal.fire({
    title: 'Are you sure?',
    type: 'warning',
    showCancelButton: true,
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    backdrop: false,
  }).then(result => {
    if (result.value) {
      Swal.fire({
        title: 'Saving...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        backdrop: false,
        onOpen: () => {
          Swal.showLoading()
        }
      })
      $.ajax({
        url: save_url,
        type: 'GET'
      }).then( response => {
        if (response.data.failed) {
          Swal.fire('Error!',`${response.data.failed}`,'error')
        } else {
          Swal.fire({
            title: 'Saved!',
            text: 'Your data has been saved.',
            type: 'success',
            backdrop: false,
          }).then(() => {
            if (objHasProp(response, 'data.redirect_to')) {
              redirect(response.data.redirect_to);
            }
          })
        }
      })
      .catch(error => {
        Swal.fire('Error!',`${error.statusText}`,'error')
      });
    }
  });
}

function salesOrderAccConfirmation(save_url) {
  Swal.fire({
    title: 'Are you sure?',
    type: 'warning',
    showCancelButton: true,
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    backdrop: false,
  }).then(result => {
    if (result.value) {
      Swal.fire({
        title: 'Saving...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        backdrop: false,
        onOpen: () => {
          Swal.showLoading()
        }
      })
      $.ajax({
        url: save_url,
        type: 'GET'
      }).then( response => {
        if(response.data.message) {
          Swal.fire({
            title: 'Warning!',
            text: response.data.message,
            type: 'info',
            backdrop: false,
          }).then(() => {
            if (objHasProp(response, 'data.redirect_to')) {
              redirect(response.data.redirect_to);
            }
          })
        } else {
          Swal.fire({
            title: 'Saved!',
            text: 'Your data has been saved.',
            type: 'success',
            backdrop: false,
          }).then(() => {
            if (objHasProp(response, 'data.redirect_to')) {
              redirect(response.data.redirect_to);
            }
          })
        }
      })
      .catch(error => {
        Swal.fire('Error!',`${error.statusText}`,'error')
      });
    }
  });
}