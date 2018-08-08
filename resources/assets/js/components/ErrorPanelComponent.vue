<template>
    <div v-if="errormsg != null && errormsg.length > 0" class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ errormsg }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</template>

<script>

export default {
    props: {
        errormsg: String,
    }
}
</script>
