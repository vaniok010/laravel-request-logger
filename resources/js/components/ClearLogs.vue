<template>
  <button @click="modal.show()" type="button" class="btn btn-outline-danger float-end">
    Clear all logs
  </button>
  <div class="modal fade" ref="clearLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="clearLogsModalLabel">Clear all logs</h4>
          <button type="button" class="btn-close" @click="modal.hide()" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete all logs?</p>
        </div>
        <div class="modal-footer">
          <button @click="modal.hide()" type="button" class="btn btn-outline-secondary">No</button>
          <button @click="this.clear()" type="button" class="btn btn-outline-danger">Yes</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {Modal} from 'bootstrap';

export default {
  data() {
    return {
      modal: null,
    }
  },

  mounted() {
    this.modal = new Modal(this.$refs.clearLogsModal)
  },

  methods: {
    clear() {
      this.$http.delete(RequestLogger.basePath + '/api/')
          .then(response => {
            this.modal.hide();
            window.location.reload();
          });
    },
  },
};
</script>
