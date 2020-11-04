<template>
  <div class="app-container">
    <el-form :inline="true" :model="search" class="demo-form-inline" @submit.native.prevent>
      <el-form-item>
        <el-input
          v-model="search.fields.name"
          placeholder="角色名"
          @keyup.enter.native="handleSearchList"
        />
      </el-form-item>
      <el-form-item>
        <el-select v-model="search.fields.status" placeholder="状态">
          <el-option label="全部状态" value />
          <el-option label="启用" value="1" />
          <el-option label="禁用" value="0" />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button
          icon="el-icon-search"
          :loading="search.searchBtnLoding"
          @click="handleSearchList"
        >查询</el-button>
        <el-button
          v-permission="'/api/roles/create'"
          type="primary"
          icon="el-icon-circle-plus-outline"
          @click="handleAdd"
        >添加</el-button>
      </el-form-item>
    </el-form>

    <el-scrollbar>
      <el-table
        :key="table.tableKey"
        v-loading="table.tableListLoading"
        :data="table.tableList"
        :border="false"
        element-loading-text="拼命加载中"
        fit
        style="width: 100%;"
      >
        <el-table-column label="角色名" prop="name" />
        <el-table-column label="描述" prop="description" />
        <el-table-column label="状态" prop="status" align="center">
          <template slot-scope="scope">
            <el-tag :type="scope.row.status | statusFilter">{{ scope.row.status ? '启用' : '禁用' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" prop="create_time" align="center" sortable />
        <el-table-column label="修改时间" prop="update_time" align="center" sortable />
        <el-table-column
          v-if="$permission(['/api/roles/edit', '/api/roles/update', '/api/roles/delete'])"
          label="操作"
          align="center"
        >
          <template slot-scope="scope">
            <el-button
              v-permission="'/api/roles/update'"
              class="action-bar-text-button-forbidden"
              type="text"
              icon="el-icon-unlock"
              size="mini"
              :loading="scope.row.editStatusBtnLoading"
              @click="handleEditStatus(scope)"
            >{{ scope.row.status ? '禁用' : '启用' }}</el-button>
            <el-button
              v-permission="'/api/roles/update'"
              class="action-bar-text-button-edit"
              type="text"
              icon="el-icon-edit"
              size="mini"
              @click="handleUpdate(scope)"
            >编辑</el-button>
            <el-popconfirm
              title="确定要删除吗？"
              placement="top"
              cancel-button-type="primary"
              confirm-button-type="text"
              @onConfirm="handleDelete(scope)"
            >
              <el-button
                slot="reference"
                v-permission="'/api/roles/delete'"
                class="action-bar-text-button-delete"
                type="text"
                :loading="scope.row.deleteBtnLoading"
                icon="el-icon-delete"
                size="mini"
              >删除</el-button>
            </el-popconfirm>
          </template>
        </el-table-column>
      </el-table>
    </el-scrollbar>

    <pagination
      :total="pagination.total"
      :page.sync="pagination.current_page"
      :limit.sync="pagination.limit"
      @pagination="handlePagenation"
    />

    <dialog-form
      :visible.sync="form.formIsVisible"
      width="30%"
      :confirm-btn-loading="form.formBtnLoading"
      @confirm="handleIssueForm"
    >
      <template>
        <el-form ref="form" :model="form.fields" :rules="form.rules" label-width="100px">
          <el-form-item label="角色名" prop="name">
            <el-input v-model="form.fields.name" placeholder="请输入角色名" />
          </el-form-item>
          <el-form-item label="描述" prop="description">
            <el-input
              v-model="form.fields.description"
              placeholder="请输入角色描述"
              type="textarea"
              :rows="2"
            />
          </el-form-item>
          <el-form-item label="状态">
            <el-switch
              v-model="form.fields.status"
              :active-value="1"
              :inactive-value="0"
              validate-event
            />
          </el-form-item>
          <el-form-item label="路由权限">
            <el-tree
              ref="routeTree"
              :data="form.treeData"
              :highlight-current="true"
              :accordion="true"
              :props="{ label: 'tree_title' }"
              :default-expanded-keys="form.fields.permissions"
              :check-strictly="true"
              style="margin-top: 7px;"
              node-key="id"
              show-checkbox
              @check="handleRouteTreeChecked"
            />
          </el-form-item>
        </el-form>
      </template>
    </dialog-form>
  </div>
</template>

<script>
import { getRoles, createRole, updateRole, deleteRole, getCreateRoleFormData, getUpdateRoleFormData } from '@/api/roles'
import Pagination from '@/components/Pagination'
import DialogForm from '@/views/components/DialogForm'

export default {
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: 'success',
        0: 'danger'
      }
      return statusMap[status]
    }
  },
  components: { Pagination, DialogForm },
  data() {
    return {
      table: {
        tableKey: 0,
        tableList: [],
        tableListLoading: true
      },
      search: {
        searchBtnLoding: false,
        fields: {
          name: '',
          status: '',
          page: 1,
          limit: 10
        }
      },
      form: {
        formBtnLoading: false,
        formIsVisible: false,
        fields: {
          name: '',
          description: '',
          status: 1,
          permissions: []
        },
        rules: {
          name: [
            { required: true, trigger: 'blur', message: '请填写角色名' }
          ],
          description: [
            { required: true, trigger: 'blur', message: '请填写描述' }
          ]
        },
        treeData: []
      },
      pagination: {
        page: 1,
        limit: 10,
        total: 0
      }
    }
  },
  created() {
    this.handleList()
  },
  methods: {
    handleList() {
      this.table.tableListLoading = true

      getRoles(this.search.fields).then(response => {
        this.table.tableListLoading = false
        this.search.searchBtnLoding = false
        this.table.tableList = response.data.data.data
        this.pagination.page = Number(response.data.data.current_page)
        this.pagination.total = Number(response.data.data.total)
        this.pagination.limit = Number(response.data.data.per_page)
      }).catch(e => {
        this.table.tableListLoading = false
        this.search.searchBtnLoding = false
      })
    },
    handleAdd() {
      getCreateRoleFormData().then(response => {
        this.form.treeData = [...response.data.data]
      })

      delete this.form.fields.id
      Object.assign(this.form.fields, {
        name: '',
        description: '',
        status: 1,
        permissions: []
      })

      this.form.formIsVisible = true
      this.$nextTick(() => {
        this.$refs.form.clearValidate()
        this.$refs.routeTree.setCheckedKeys([])
      })
    },
    handleUpdate(scope) {
      getUpdateRoleFormData(scope.row.id).then(response => {
        this.form.treeData = [...response.data.data]
      })

      const permissions = []
      for (const permission of scope.row.permissions) {
        permissions.push(permission.id)
      }

      Object.assign(this.form.fields, {
        id: scope.row.id,
        name: scope.row.name,
        description: scope.row.description,
        status: scope.row.status,
        permissions: permissions
      })

      this.form.formIsVisible = true
      this.$nextTick(() => {
        this.$refs.form.clearValidate()
        this.$refs.routeTree.setCheckedKeys(permissions)
      })
    },
    handleDelete(scope) {
      this.$set(scope.row, 'deleteBtnLoading', true)

      deleteRole(scope.row.id).then(response => {
        this.$delete(scope.row, 'deleteBtnLoading')
        if (response.data.code === 'OK') {
          this.$message.success('删除成功')
          this.handleList()
        }
      }).catch(e => {
        this.$delete(scope.row, 'deleteBtnLoading')
      })
    },
    handlePagenation(pagenation) {
      this.search.fields.page = pagenation.page
      this.search.fields.limit = pagenation.limit
      this.handleList()
    },
    handleSearchList() {
      this.search.searchBtnLoding = true
      this.handleList()
    },
    handleIssueForm() {
      this.$refs.form.validate(valid => {
        if (!valid) return false

        this.form.formBtnLoading = true

        if (this.form.fields.id) {
          updateRole(this.form.fields).then(response => {
            this.handleFormSuccess(response.data.code)
          }).catch(e => {
            this.form.formBtnLoading = false
          })
        } else {
          createRole(this.form.fields).then(response => {
            this.handleFormSuccess(response.data.code)
          }).catch(e => {
            this.form.formBtnLoading = false
          })
        }
        this.form.formIsVisible = true
      })
    },
    handleFormSuccess(code) {
      this.form.formBtnLoading = false

      if (code === 'OK') {
        this.form.formIsVisible = false
        this.$message.success('数据保存成功')
        this.handleList()
      }
    },
    handleEditStatus(scope) {
      this.$set(scope.row, 'editStatusBtnLoading', true)

      const willDoAction = scope.row.status ? '禁用' : '启用'
      const params = {
        id: scope.row.id,
        status: Number(!scope.row.status)
      }

      updateRole(params).then(response => {
        this.$delete(scope.row, 'editStatusBtnLoading')
        if (response.data.code === 'OK') {
          scope.row.status = Number(!scope.row.status)
          this.$message.success(`成功${willDoAction}`)
        }
      }).catch(e => {
        this.$delete(scope.row, 'editStatusBtnLoading')
      })
    },
    handleRouteTreeChecked(node) {
      this.handleTreeChecked(this.$refs.routeTree, node.id)
      this.form.fields.permissions = this.$refs.routeTree.getCheckedKeys()
    },
    handleTreeChecked(Tree, nodeKey) {
      const checkedNode = Tree.getNode(nodeKey)

      const recursiveChildrenChecked = (node) => {
        if (node.childNodes.length !== 0) {
          for (const childNode of node.childNodes) {
            Tree.setChecked(childNode.data.id, node.checked)
            if (childNode.childNodes.length !== 0) {
              recursiveChildrenChecked(childNode)
            }
          }
        }
      }

      const recursiveParentChecked = (node) => {
        if (node.parent.key !== undefined) {
          if (node.checked) {
            Tree.setChecked(node.parent.data.id, node.checked)
            recursiveParentChecked(node.parent)
          } else {
            if (isAllChildrenNoChecked(node.parent)) {
              Tree.setChecked(node.parent.data.id, node.checked)
              recursiveParentChecked(node.parent)
            }
          }
        }
      }

      const isAllChildrenNoChecked = (parentNode) => {
        let checkedCount = 0
        for (const childNode of parentNode.childNodes) {
          if (childNode.checked) checkedCount += 1
        }
        return checkedCount === 0
      }

      if (!checkedNode.isLeaf) {
        recursiveChildrenChecked(checkedNode)
        recursiveParentChecked(checkedNode)
      } else {
        recursiveParentChecked(checkedNode)
      }
    }
  }
}
</script>

<style lang="scss" scope>
.el-tree {
  .el-tree__empty-text {
    left: 0;
    top: -7px;
    transform: none;
  }
}

.action-bar-text-button-delete {
  margin-left: 10px;
}
</style>
