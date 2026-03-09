<template>
    <div class="row">
        <div class="col-6">
            <a @click="showFilters = !showFilters" class="btn btn-outline-primary float-start">
                <span v-if="loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                Filters & Sorting
            </a>
        </div>
        <div class="col-6">
            <ClearLogs/>
        </div>
    </div>
    <div v-if="showFilters">
        <div class="card card-body mt-3">
            <form>
                <div class="row">
                    <!-- URI Filter -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label for="uri" class="col-sm-4 col-form-label text-nowrap">URI</label>
                            <div class="col-sm-8">
                                <input v-model="filters.uri" type="text" class="form-control" id="uri"/>
                            </div>
                        </div>
                    </div>

                    <!-- Exclude URIs -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label class="col-sm-4 col-form-label text-nowrap">Exclude URIs</label>
                            <div class="col-sm-8">
                                <div v-for="(uri, index) in filters.excludeUris" :key="index" class="d-flex gap-1 pb-1">
                                    <input v-model="filters.excludeUris[index]" type="text" class="form-control"/>
                                    <button v-if="index === 0" type="button" class="btn btn-outline-success"
                                            @click="addExcludeUri">
                                        +
                                    </button>
                                    <button v-else type="button" class="btn btn-outline-danger"
                                            @click="removeExcludeUri(index)">
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Response Status Code -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label for="response_status" class="col-sm-4 col-form-label text-nowrap">Response
                                Status</label>
                            <div class="col-sm-8">
                                <input v-model.number="filters.responseStatus" type="number" class="form-control"
                                       id="response_status"/>
                            </div>
                        </div>
                    </div>

                    <!-- Exclude Response Statuses -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label class="col-sm-4 col-form-label text-nowrap">Exclude Response Statuses</label>
                            <div class="col-sm-8">
                                <div v-for="(status, index) in filters.excludeResponseStatuses" :key="index"
                                     class="d-flex gap-1 pb-1">
                                    <input v-model="filters.excludeResponseStatuses[index]" type="number"
                                           class="form-control"/>
                                    <button v-if="index === 0" type="button" class="btn btn-outline-success"
                                            @click="addExcludeResponseStatus">
                                        +
                                    </button>
                                    <button v-else type="button" class="btn btn-outline-danger"
                                            @click="removeExcludeResponseStatus(index)">
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fingerprint -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label for="fingerprint" class="col-sm-4 col-form-label text-nowrap">Fingerprint</label>
                            <div class="col-sm-8">
                                <input v-model="filters.fingerprint" type="text" class="form-control" id="fingerprint"/>
                            </div>
                        </div>
                    </div>

                    <!-- Exclude Fingerprints -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label class="col-sm-4 col-form-label text-nowrap">Exclude Fingerprints</label>
                            <div class="col-sm-8">
                                <div v-for="(fingerprint, index) in filters.excludeFingerprints" :key="index"
                                     class="d-flex gap-1 pb-1">
                                    <input v-model="filters.excludeFingerprints[index]" type="text"
                                           class="form-control"/>
                                    <button v-if="index === 0" type="button" class="btn btn-outline-success"
                                            @click="addExcludeFingerprint">
                                        +
                                    </button>
                                    <button v-else type="button" class="btn btn-outline-danger"
                                            @click="removeExcludeFingerprint(index)">
                                        ×
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- HTTP Methods -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label class="col-sm-4 col-form-label text-nowrap">Method</label>
                            <div class="col-sm-8 pt-1">
                                <div v-for="method in ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']"
                                     :key="method"
                                     class="form-check form-check-inline">
                                    <input v-model="filters.methods" class="form-check-input" type="checkbox"
                                           :id="method" :value="method"/>
                                    <label class="form-check-label" :for="method">{{ method }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label class="col-sm-4 col-form-label text-nowrap">Sent</label>
                            <div class="col-sm-8">
                                <div class="row">
                                    <div class="col-xl-6 col-12">
                                        <div class="input-group mb-2 mb-xl-0">
                                            <label for="sent_from" class="input-group-text">From</label>
                                            <input v-model="filters.sentFrom" type="datetime-local" class="form-control"
                                                   id="sent_from"/>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-12">
                                        <div class="input-group">
                                            <label for="sent_to" class="input-group-text">To</label>
                                            <input v-model="filters.sentTo" type="datetime-local" class="form-control"
                                                   id="sent_to"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label class="col-sm-4 col-form-label text-nowrap">Duration</label>
                            <div class="col-sm-8">
                                <div class="row">
                                    <div class="col-xl-6 col-12">
                                        <div class="input-group mb-2 mb-xl-0">
                                            <label for="duration_from" class="input-group-text">From</label>
                                            <input v-model="filters.durationFrom" type="number" class="form-control"
                                                   id="duration_from"/>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-12">
                                        <div class="input-group">
                                            <label for="duration_to" class="input-group-text">To</label>
                                            <input v-model="filters.durationTo" type="number" class="form-control"
                                                   id="duration_to"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Memory usage -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label class="col-sm-4 col-form-label text-nowrap">Memory usage</label>
                            <div class="col-sm-8">
                                <div class="row">
                                    <div class="col-xl-6 col-12">
                                        <div class="input-group mb-2 mb-xl-0">
                                            <label for="memory_from" class="input-group-text">From</label>
                                            <input v-model="filters.memoryFrom" type="number" class="form-control"
                                                   id="memory_from"/>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-12">
                                        <div class="input-group">
                                            <label for="memory_to" class="input-group-text">To</label>
                                            <input v-model="filters.memoryTo" type="number" class="form-control"
                                                   id="memory_to"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- IP -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label for="ip" class="col-sm-4 col-form-label text-nowrap">IP</label>
                            <div class="col-sm-8">
                                <input v-model="filters.ip" type="text" class="form-control"
                                       id="ip"/>
                            </div>
                        </div>
                    </div>

                    <!-- Sorting Options -->
                    <div class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label class="col-sm-4 col-form-label text-nowrap">Sort By</label>
                            <div class="col-sm-8">
                                <select v-model="filters.order" class="form-control">
                                    <option value="sent|desc">Sent (Newest First)</option>
                                    <option value="sent|asc">Sent (Oldest First)</option>
                                    <option value="response_status|desc">Status (High to Low)</option>
                                    <option value="response_status|asc">Status (Low to High)</option>
                                    <option value="duration|desc">Duration (Longest First)</option>
                                    <option value="duration|asc">Duration (Shortest First)</option>
                                    <option value="memory|desc">Memory (High to Low)</option>
                                    <option value="memory|asc">Memory (Low to High)</option>
                                    <option value="repeats|desc">Repeats (High to Low)</option>
                                    <option value="repeats|asc">Repeats (Low to High)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Custom fields -->
                    <div v-for="field in RequestLogger.customFields" :key="field" class="col-xl-6 col-12">
                        <div class="form-group row pb-2">
                            <label :for="'custom_' + field" class="col-sm-4 col-form-label text-nowrap">{{
                                    jsConvert.toHeaderCase(field)
                                }}</label>
                            <div class="col-sm-8">
                                <input v-model="filters.customFields[field]" class="form-control"
                                       :id="'custom_' + field"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group row">
                    <div class="col-12 text-center pt-2">
                        <button @click="applyFilters" type="button" class="btn btn-outline-success px-5">Apply Filters
                        </button>
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
    props: {
        loading: {
            type: Boolean,
            default: false,
        }
    },
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
                excludeResponseStatuses: [""],
                fingerprint: null,
                excludeFingerprints: [""],
                sentFrom: null,
                sentTo: null,
                durationFrom: null,
                durationTo: null,
                memoryFrom: null,
                memoryTo: null,
                ip: null,
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
        addExcludeResponseStatus() {
            this.filters.excludeResponseStatuses.push("");
        },
        removeExcludeResponseStatus(index) {
            this.filters.excludeResponseStatuses.splice(index, 1);
        },
        applyFilters() {
            this.showFilters = false;
            this.$emit("filter-changed", this.filters);
        },
    },
};
</script>
