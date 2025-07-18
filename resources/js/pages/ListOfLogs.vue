<template>
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h4>Request Logs</h4>
  </div>
  <div>
    <Filters @filter-changed="updateFilters"/>
  </div>
  <div class="card card-body mt-3">
    <h5 class="card-title">Total: {{ total }}</h5>
    <div class="table-responsive">
      <table id="request-logs" class="table align-middle">
        <thead>
        <tr>
          <th>URI</th>
          <th>Status</th>
          <th>Duration</th>
          <th>Memory usage</th>
          <th>IP</th>
          <th>Sent</th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="log in logs">
          <td class="column-uri">
            <div>
              <b :class="this.cssClassOfMethod(log.method)">{{ log.method }}</b> {{ log.decoded_uri }}
            </div>
            <div>
              <span class="badge bg-secondary rounded-pill">{{ log.repeats }}</span>
              {{ log.fingerprint }}
            </div>
          </td>
          <td class="column-status-code">
            <span :class="this.cssClassOfStatus(log.response_status)" class="border border-2 rounded p-1">
              {{ log.response_status }}
            </span>
          </td>
          <td class="column-duration">{{ log.duration }} ms</td>
          <td class="column-memory">{{ log.memory }} MB</td>
          <td>{{ log.ip }}</td>
          <td>
            <div>{{ log.formatted_time }}</div>
            <div>{{ log.formatted_date }}</div>
          </td>
          <td>
            <div class="d-flex justify-content-end">
              <router-link :to="{name: 'one', params: {id: log.id}}" target="_blank">
                <button type="button" class="btn btn-outline-primary btn-sm">View</button>
              </router-link>
            </div>
          </td>
        </tr>
        </tbody>
        <tfoot>
        <Pagination :total-pages="totalPages" :start-page="currentPage" @page-changed="updatePage"/>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script>
import Pagination from "../components/Pagination.vue";
import Filters from "../components/Filters.vue";

export default {
  components: {
    Pagination,
    Filters,
  },

  data() {
    return {
      filters: {},
      total: 0,
      logs: [],
      currentPage: 1,
      totalPages: 1,
    };
  },

  mounted() {
    this.loadRequestLogs();
  },

  computed: {
    filterData() {
      let order = this.filters.order ?? 'sent|desc';

      return {
        sentFrom: this.filters.sentFrom || null,
        sentTo: this.filters.sentTo || null,
        excludeFingerprints: (this.filters.excludeFingerprints || []).filter(String),
        excludeUris: (this.filters.excludeUris || []).filter(String),
        fingerprint: this.filters.fingerprint || null,
        methods: (this.filters.methods || []).filter(String),
        responseStatus: this.filters.responseStatus || null,
        uri: this.filters.uri || null,
        durationFrom: this.filters.durationFrom || null,
        durationTo: this.filters.durationTo || null,
        memoryFrom: this.filters.memoryFrom || null,
        memoryTo: this.filters.memoryTo || null,
        ip: this.filters.ip || null,
        customFields: this.filters.customFields || null,
        orderBy: order.split('|')[0],
        orderDir: order.split('|')[1],
      };
    }
  },

  methods: {
    loadRequestLogs() {
      this.$http.post(RequestLogger.basePath + `/api/list?page=${this.currentPage}`, this.filterData)
          .then(response => {
            this.logs = response.data.data;
            this.totalPages = response.data.last_page;
            this.total = response.data.total;
          });
    },

    updateFilters(filters) {
      this.filters = filters;
      this.currentPage = 1; // Reset page to 1 when filters change
      this.loadRequestLogs();
    },

    updatePage(page) {
      this.currentPage = page;
      this.loadRequestLogs();
    },

    cssClassOfStatus(statusCode) {
      if (statusCode < 300) {
        return 'border-success';
      }
      if (statusCode >= 300 && statusCode < 500) {
        return 'border-warning';
      }
      if (statusCode >= 500 && statusCode < 600) {
        return 'border-danger';
      }
      return 'border-dark';
    },

    cssClassOfMethod(method) {
      switch (method) {
        case 'GET':
          return 'text-success';
        case 'POST':
          return 'text-warning';
        case 'PUT':
          return 'text-primary';
        case 'PATCH':
          return 'text-primary';
        case 'DELETE':
          return 'text-danger';
        default:
          return '';
      }
    },
  }
}
</script>
