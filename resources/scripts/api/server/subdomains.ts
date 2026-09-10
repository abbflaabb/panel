import http from '@/api/http';

export interface SubdomainItem {
    id: number;
    fqdn: string;
    allocation: string;
    allocationId: number;
    domainId: number;
}

export interface SubdomainDomain {
    id: number;
    name: string;
}

export interface SubdomainAllocation {
    id: number;
    address: string;
    target: string;
    port: number;
}

export interface SubdomainPayload {
    subdomains: SubdomainItem[];
    domains: SubdomainDomain[];
    allocations: SubdomainAllocation[];
}

export const getSubdomains = async (uuid: string): Promise<SubdomainPayload> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/subdomains`);
    return data;
};

export const createSubdomain = async (
    uuid: string,
    label: string,
    domainId: number,
    allocationId: number
): Promise<SubdomainItem> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/subdomains`, { label, domainId, allocationId });
    return data;
};

export const deleteSubdomain = async (uuid: string, id: number): Promise<void> => {
    await http.delete(`/api/client/servers/${uuid}/subdomains/${id}`);
};
