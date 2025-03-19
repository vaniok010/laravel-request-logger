<template>
  <div class="row">
    <div class="col-6">
      <a @click="showFilters = !showFilters" class="btn btn-outline-primary float-start">Filters & Sorting</a>
    </div>
    <div class="col-6">
      <ClearLogs/>
    </div>
  </div>
  <div v-if="showFilters">
    <div class="card card-body mt-3">
      <form>
        <!-- URI Filter -->
        <div class="form-group row pb-3">
          <label for="uri" class="col-sm-2 col-form-label">URI</label>
          <div class="col-sm-10">
            <input v-model="filters.uri" type="text" class="form-control" id="uri"/>
          </div>
        </div>

        <!-- Exclude URIs -->
        <div class="form-group row pb-3">
          <label class="col-sm-2 col-form-label">Exclude URIs</label>
          <div class="col-sm-10">
            <div v-for="(uri, index) in filters.excludeUris" :key="index" class="d-flex gap-1 pb-1">
              <input v-model="filters.excludeUris[index]" type="text" class="form-control"/>
              <button v-if="index === 0" type="button" class="btn btn-outline-success" @click="addExcludeUri">
                +
              </button>
              <button v-else type="button" class="btn btn-outline-danger" @click="removeExcludeUri(index)">
                ×
              </button>
            </div>
          </div>
        </div>

        <!-- HTTP Methods -->
        <div class="form-group row pb-3">
          <label class="col-sm-2 col-form-label">Method</label>
          <div class="col-sm-10">
            <div v-for="method in ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']" :key="method"
                 class="form-check form-check-inline">
              <input v-model="filters.methods" class="form-check-input" type="checkbox" :id="method" :value="method"/>
              <label class="form-check-label" :for="method">{{ method }}</label>
            </div>
          </div>
        </div>

        <!-- Response Status Code -->
        <div class="form-group row pb-3">
          <label for="response_status" class="col-sm-2 col-form-label">Response Status</label>
          <div class="col-sm-10">
            <input v-model.number="filters.responseStatus" type="number" class="form-control"
                   id="response_status"/>
          </div>
        </div>

        <!-- Custom fields -->
        <div v-for="field in RequestLogger.customFields" class="form-group row pb-3">
          <label :for="'custom_' + field" class="col-sm-2 col-form-label">{{ jsConvert.toHeaderCase(field) }}</label>
          <div class="col-sm-10">
            <input v-model="filters.customFields[field]" class="form-control" :id="'custom_' + field"/>
          </div>
        </div>

        <!-- Fingerprint -->
        <div class="form-group row pb-3">
          <label for="fingerprint" class="col-sm-2 col-form-label">Fingerprint</label>
          <div class="col-sm-10">
            <input v-model="filters.fingerprint" type="text" class="form-control" id="fingerprint"/>
          </div>
        </div>

        <!-- Exclude Fingerprints -->
        <div class="form-group row pb-3">
          <label class="col-sm-2 col-form-label">Exclude Fingerprints</label>
          <div class="col-sm-10">
            <div v-for="(fingerprint, index) in filters.excludeFingerprints" :key="index" class="d-flex gap-1 pb-1">
              <input v-model="filters.excludeFingerprints[index]" type="text" class="form-control"/>
              <button v-if="index === 0" type="button" class="btn btn-outline-success" @click="addExcludeFingerprint">
                +
              </button>
              <button v-else type="button" class="btn btn-outline-danger" @click="removeExcludeFingerprint(index)">
                ×
              </button>
            </div>
          </div>
        </div>

        <!-- Date Range -->
        <div class="form-group row pb-3">
          <label class="col-sm-2 col-form-label">Sent</label>
          <div class="col-sm-10">
            <div class="row">
              <div class="col-sm-6">
                <div class="input-group mb-3">
                  <label for="sent_from" class="input-group-text">From</label>
                  <input v-model="filters.sentFrom" type="datetime-local" class="form-control" id="sent_from"/>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="input-group mb-3">
                  <label for="sent_to" class="input-group-text">To</label>
                  <input v-model="filters.sentTo" type="datetime-local" class="form-control" id="sent_to"/>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Duration -->
        <div class="form-group row pb-3">
          <label class="col-sm-2 col-form-label">Duration</label>
          <div class="col-sm-10">
            <div class="row">
              <div class="col-sm-6">
                <div class="input-group mb-3">
                  <label for="duration_from" class="input-group-text">From</label>
                  <input v-model="filters.durationFrom" type="number" class="form-control" id="duration_from"/>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="input-group mb-3">
                  <label for="duration_to" class="input-group-text">To</label>
                  <input v-model="filters.durationTo" type="number" class="form-control" id="duration_to"/>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Memory usage -->
        <div class="form-group row pb-3">
          <label class="col-sm-2 col-form-label">Memory usage</label>
          <div class="col-sm-10">
            <div class="row">
              <div class="col-sm-6">
                <div class="input-group mb-3">
                  <label for="memory_from" class="input-group-text">From</label>
                  <input v-model="filters.memoryFrom" type="number" class="form-control" id="memory_from"/>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="input-group mb-3">
                  <label for="memory_to" class="input-group-text">To</label>
                  <input v-model="filters.memoryTo" type="number" class="form-control" id="memory_to"/>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sorting Options -->
        <div class="form-group row pb-3">
          <label class="col-sm-2 col-form-label">Sort By</label>
          <div class="col-sm-10">
            <select v-model="filters.order" class="form-control">
              <option value="sent|desc">Sent (Newest First)</option>
              <option value="sent|asc">Sent (Oldest First)</option>
              <option value="response_status|desc">Status (High to Low)</option>
              <option value="response_status|asc">Status (Low to High)</option>
              <option value="duration|desc">Duration (Longest First)</option>
              <option value="duration|asc">Duration (Shortest First)</option>
              <option value="memory|desc">Memory (High to Low)</option>
              <option value="memory|asc">Memory (Low to High)</option>
            </select>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="form-group row pb-3">
          <label class="col-sm-2 col-form-label"></label>
          <div class="col-sm-10">
            <button @click="applyFilters" type="button" class="btn btn-outline-success">Apply Filters</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import ClearLogs from "./ClearLogs.vue";
import jsConvert from "js-convert-case";

export default {
  computed: {
    jsConvert() {
      return jsConvert
    }
  },
  components: {
    ClearLogs,
  },

  data() {
    return {
      showFilters: false,
      filters: {
        uri: null,
        excludeUris: [""],
        methods: [],
        responseStatus: null,
        fingerprint: null,
        excludeFingerprints: [""],
        sentFrom: null,
        sentTo: null,
        durationFrom: null,
        durationTo: null,
        memoryFrom: null,
        memoryTo: null,
        customFields: {},
        order: "sent|desc",
      },
    };
  },

  methods: {
    addExcludeUri() {
      this.filters.excludeUris.push("");
    },
    removeExcludeUri(index) {
      this.filters.excludeUris.splice(index, 1);
    },
    addExcludeFingerprint() {
      this.filters.excludeFingerprints.push("");
    },
    removeExcludeFingerprint(index) {
      this.filters.excludeFingerprints.splice(index, 1);
    },
    applyFilters() {
      this.showFilters = false;
      this.$emit("filter-changed", this.filters);
    },
  },
};
</script>
